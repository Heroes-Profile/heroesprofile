<?php

namespace App\Services\HelpDigest;

use App\Http\Controllers\Global\GlobalCompositionsController;
use App\Http\Controllers\Global\GlobalDraftController;
use App\Http\Controllers\Global\GlobalHeroMapStatsController;
use App\Http\Controllers\Global\GlobalHeroMatchupStatsController;
use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Http\Controllers\Global\GlobalLeaderboardController;
use App\Http\Controllers\Global\GlobalPartyStatsController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
use App\Http\Controllers\Player\FriendFoeController;
use App\Http\Controllers\Player\PlayerAwardsController;
use App\Http\Controllers\Player\PlayerController;
use App\Http\Controllers\Player\PlayerHeroesMapsRolesController;
use App\Http\Controllers\Player\PlayerMatchHistory;
use App\Http\Controllers\Player\PlayerMatchupsController;
use App\Http\Controllers\Player\PlayerMMRController;
use App\Http\Controllers\Player\PlayerPartyController;
use App\Http\Controllers\Player\PlayerTalentsController;
use App\Http\Controllers\Tools\ActivityGraphsController;
use App\Http\Controllers\XalatathRecapController;
use App\Services\FaqMatcher;
use App\Services\GlobalDataService;
use App\Services\HeroPageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

// Daily snapshot of each page's default view, for the help chat to quote from
class HelpDigestBuilder
{
    public const INDEX_KEY = 'help_digest:index';

    public const CHUNK_PREFIX = 'help_digest:chunk:';

    // Outlives a missed run, and PruneDatabaseTables only removes expired rows
    private const TTL_DAYS = 3;

    // Cold global queries started per run; the rest wait for the next run
    private const MAX_QUEUED = 60;

    // Longest list kept from any table, and the most text one chunk can hold
    private const MAX_ROWS = 25;

    private const MAX_CHARS = 8000;

    private const ACTIVITY_MONTHS = 24;

    private const ROLES = ['Bruiser', 'Healer', 'Melee Assassin', 'Ranged Assassin', 'Support', 'Tank'];

    // Zemill#1940, the FAQ's example profile
    private const SAMPLE_PLAYER = ['battletag' => 'Zemill', 'blizz_id' => 67280, 'region' => 1];

    private const SAMPLE_HERO = 'Abathur';

    private const SAMPLE_MAP = 'Cursed Hollow';

    private const SAMPLE_ROLE = 'Bruiser';

    private const SAMPLE_NOTE = 'Sample profile (Zemill#1940): shows what this page contains. Never quote these numbers.';

    // Pages with no default data: their page-heading text, from the .vue files. The faq.php description is added on build.
    private const HEADINGS = [
        '/Global/Matchups/Talents' => ['Hero Matchup Talents Statistics', 'This page allows you to look at talent win rates and popularity, for a hero against, or with another hero. Click "enemy" for games where the two heroes played against each other, "ally" for games where they were on the same team. This page may take longer to load than the others.'],
        '/Global/Talents/Builder' => ['Talent Calculator & Builder', 'Pick a talent in any tier to start. Talent win rates are recalculated as you change your build. Build win chance, win rate and games played are shown below the talents.'],
        '/Tools/AutoBattler' => ['Auto Battler', 'Build two AI teams of five heroes with full talent builds, then download the AI build files and watch them battle it out in the Veteran Introduction.'],
        '/Match/Single/{replay ID}' => ['Single Match', null],
        '/PreMatch/Results' => ['PreMatch', null],
        '/Analytics' => ['Analytics (Pre-Alpha)', 'Deep replay analysis of your own season games. Pre-alpha: this is in very early development. Numbers, views and features will change, and some may be wrong or missing.'],
        '/Profile/Settings' => ['Settings', null],
        '/Upload' => ['Upload Replays', 'Upload replays from your browser, or install the uploader and have it done for you as you play.'],
        '/Contact' => ['Contact', 'If you find an issue on the website, or have general questions, please use the contact form or email us.'],
        '/FAQ' => ['FAQ', 'Frequently asked questions about Heroes Profile. Can\'t find your answer? Ask us below.'],
        '/Api/Docs' => ['Docs', 'Every endpoint, generated from the routes themselves, so this page cannot describe an endpoint that does not exist.'],
        '/Github/Change/Log' => ['Change Log', null],
        '/Twitch' => ['Streamers on Twitch', 'Heroes of the Storm streamers using the Heroes Profile Twitch extension. Live channels are listed first.'],
        '/Esports' => ['Community Esports', 'Click a league to see overall stats, divisions, teams, matches and unofficial rankings.'],
        '/Esports/NGS' => ['NGS', 'Heroes of the Storm statistics and comparison for the Nexus Gaming Series.'],
        '/Esports/CCL' => ['CCL', 'Heroes of the Storm statistics and comparison for the Community Clash League.'],
        '/Esports/MastersClash' => ['Masters Clash', 'Heroes of the Storm statistics and comparison for the Masters Clash.'],
        '/Esports/HeroesInternational' => ['Heroes International', 'Click a league to see main Heroes International stats, or Nations Cup stats.'],
        '/Esports/NutCup' => ['Nut Cup', 'Heroes of the Storm statistics and comparison for the Nut Cup.'],
        '/Esports/Other' => ['HGC and More', 'Past tournaments and series, with a filterable list of replays.'],
    ];

    // Build groups, in run order. Each returns chunks; one marked pending keeps yesterday's copy.
    private const GROUPS = [
        'heroStats', 'compositions', 'party', 'leaderboards', 'heroMaps', 'matchups', 'draft', 'talents',
        'heroPages', 'player', 'activity', 'xalatathRecap', 'headings',
    ];

    private string $asOf;

    private int $queued = 0;

    private ?array $pageDescriptions = null;

    public function __construct(private GlobalDataService $globalDataService) {}

    /**
     * @return array{built: int, kept: int, pending: int, failed: array<int, string>}
     */
    public function build(): array
    {
        $cache = Cache::store('database');
        $ttl = now()->addDays(self::TTL_DAYS);
        $this->asOf = now()->utc()->toIso8601ZuluString();
        $this->queued = 0;

        $previous = collect($cache->get(self::INDEX_KEY, []))->keyBy('id');
        $index = [];
        $summary = ['built' => 0, 'kept' => 0, 'pending' => 0, 'failed' => []];

        $keep = function (array $entry) use ($cache, $ttl, &$index, &$summary) {
            $chunk = $cache->get(self::CHUNK_PREFIX.$entry['id']);
            if ($chunk === null) {
                $summary['pending']++;

                return;
            }
            $cache->put(self::CHUNK_PREFIX.$entry['id'], $chunk, $ttl);
            $index[] = $entry;
            $summary['kept']++;
        };

        foreach (self::GROUPS as $group) {
            try {
                $chunks = $this->{$group}();
            } catch (\Throwable $e) {
                Log::warning('Help digest group failed', ['group' => $group, 'message' => $e->getMessage()]);
                $summary['failed'][] = $group;
                $previous->where('group', $group)->each($keep);

                continue;
            }

            foreach ($chunks as $chunk) {
                if (! empty($chunk['pending'])) {
                    $previous->has($chunk['id']) ? $keep($previous[$chunk['id']]) : $summary['pending']++;

                    continue;
                }

                $chunk += ['hero' => null, 'filters' => null, 'as_of' => $this->asOf];
                $chunk['text'] = mb_substr($chunk['text'], 0, self::MAX_CHARS);
                $cache->put(self::CHUNK_PREFIX.$chunk['id'], $chunk, $ttl);
                $index[] = [
                    'id' => $chunk['id'],
                    'group' => $group,
                    'title' => $chunk['title'],
                    'url' => $chunk['url'],
                    'hero' => $chunk['hero'],
                    'description' => $this->description($chunk['url']),
                    // For matching questions without loading every chunk
                    'summary' => mb_substr($chunk['text'], 0, 400),
                    'as_of' => $chunk['as_of'],
                    'tokens' => (int) ceil(strlen($chunk['text']) / 4),
                ];
                $summary['built']++;
            }
        }

        // Last, so a reader never sees half a digest
        $cache->put(self::INDEX_KEY, $index, $ttl);

        return $summary;
    }

    private function heroStats(): array
    {
        $data = $this->global(GlobalHeroStatsController::class, 'getGlobalHeroData', $this->globalDefaults() + [
            'statfilter' => 'win_rate',
            'groupsize' => [],
        ]);
        if ($data === null) {
            return [$this->pending('global-hero'), ...$this->heroes()->map(fn ($hero) => $this->pending('global-hero-'.$hero->id))];
        }

        $line = fn ($hero) => sprintf(
            '%s (%s): win %s%%, pick %s%%, ban %s%%, popularity %s%%, change %+.2f, games %s',
            $hero['name'], $hero['role'], $hero['win_rate'], $hero['pick_rate'], $hero['ban_rate'],
            $hero['popularity'], $hero['win_rate_change'], number_format($hero['games_played'])
        );
        $filters = $this->globalFilters();
        $averages = 'Average win rate '.$data['average_win_rate'].'%, pick rate '.$data['average_pick_rate'].'%, ban rate '.$data['average_ban_rate'].'%. Change is the win rate change from the previous patch.';

        $chunks = [$this->chunk('global-hero', 'Hero Stats', '/Global/Hero', $filters, [
            $averages,
            'Every hero, sorted by win rate.',
            ...collect($data['data'] ?? [])->map($line),
        ])];

        foreach ($data['data'] ?? [] as $rank => $hero) {
            $chunks[] = $this->chunk('global-hero-'.$hero['hero_id'], $hero['name'].' Hero Stats', '/Global/Hero?hero='.rawurlencode($hero['name']), $filters, [
                $line($hero),
                'Rank '.($rank + 1).' of '.count($data['data']).' by win rate. '.$averages,
            ], $hero['name']);
        }

        return $chunks;
    }

    private function compositions(): array
    {
        $data = $this->global(GlobalCompositionsController::class, 'getCompositionsData', $this->globalDefaults() + [
            'minimum_games' => 100,
        ]);
        if ($data === null) {
            return [$this->pending('global-compositions')];
        }

        $lines = collect($data)->map(fn ($row) => sprintf(
            '%s: win %s%%, popularity %s%%, games %s',
            collect(['role_one', 'role_two', 'role_three', 'role_four', 'role_five'])->map(fn ($slot) => data_get($row[$slot], 'name', $row[$slot]))->implode(', '),
            $row['win_rate'], $row['popularity'], number_format($row['games_played'])
        ));

        return [$this->chunk('global-compositions', 'Compositions', '/Global/Compositions', $this->globalFilters().', at least 100 games', [
            'Team compositions by role, sorted by win rate.',
            ...$lines,
        ])];
    }

    private function party(): array
    {
        $data = $this->global(GlobalPartyStatsController::class, 'getPartyStats', $this->globalDefaults());
        if ($data === null) {
            return [$this->pending('global-party')];
        }

        return [$this->chunk('global-party', 'Party Stats', '/Global/Party', $this->globalFilters(), [
            'Win rates by party size, as the page receives them:',
            $this->json($data),
        ])];
    }

    private function leaderboards(): array
    {
        $season = $this->globalDataService->getDefaultSeason();
        $gameType = $this->globalDataService->getGameTypeDefault('single')[0];
        $base = ['season' => $season, 'game_type' => $gameType, 'groupsize' => 'Solo', 'hero' => 1, 'role' => 'Bruiser'];
        $filters = 'Season '.$season.', '.$this->gameTypeName([$gameType]).', solo queue';

        $chunks = [];
        $add = function (string $id, string $title, array $parameters, ?string $hero = null) use (&$chunks, $base, $filters) {
            $rows = $this->call(GlobalLeaderboardController::class, 'getLeaderboardData', $parameters + $base);
            if ($rows === null || isset($rows['status'])) {
                $chunks[] = $this->pending($id);

                return;
            }

            $chunks[] = $this->chunk($id, $title, '/Global/Leaderboard', $filters, [
                'Top '.self::MAX_ROWS.' by Heroes Profile Rating.',
                ...collect($rows)->take(self::MAX_ROWS)->map(fn ($row) => sprintf(
                    '#%s %s (%s): rating %s, MMR %s, win %s%%, games %s, most played %s',
                    data_get($row, 'rank'), data_get($row, 'split_battletag', data_get($row, 'battletag')), data_get($row, 'region'),
                    round((float) data_get($row, 'rating'), 2), round((float) data_get($row, 'conservative_rating')),
                    data_get($row, 'win_rate'), data_get($row, 'games_played'), data_get($row, 'most_played_hero.name', 'none')
                )),
            ], $hero);
        };

        $add('leaderboard-player', 'Player Leaderboard', ['type' => 'player']);
        foreach (self::ROLES as $role) {
            $add('leaderboard-role-'.str_replace(' ', '-', strtolower($role)), $role.' Role Leaderboard', ['type' => 'role', 'role' => $role]);
        }
        foreach ($this->heroes() as $hero) {
            $add('leaderboard-hero-'.$hero->id, $hero->name.' Hero Leaderboard', ['type' => 'hero', 'hero' => $hero->id], $hero->name);
        }

        // Real Storm League ranks: its own shape
        $rows = $this->call(GlobalLeaderboardController::class, 'getLeaderboardData', ['type' => 'sl rank'] + $base);
        $chunks[] = $rows === null || isset($rows['status'])
            ? $this->pending('leaderboard-sl-rank')
            : $this->chunk('leaderboard-sl-rank', 'Storm League Rank Leaderboard', '/Global/Leaderboard', 'Season '.$season.', real Storm League ranks', [
                'Top '.self::MAX_ROWS.' by real Storm League rank:',
                $this->json($rows),
            ]);

        return $chunks;
    }

    private function heroMaps(): array
    {
        return $this->perHero('global-hero-maps', 'Hero Map Stats', '/Global/Hero/Maps/', GlobalHeroMapStatsController::class, 'getHeroStatMapData', $this->globalDefaults(), 'How this hero does on each map.');
    }

    private function matchups(): array
    {
        return $this->perHero('global-matchups', 'Matchups', '/Global/Matchups/', GlobalHeroMatchupStatsController::class, 'getHeroMatchupData', $this->globalDefaults(), 'Win rates with each hero as an ally and against each as an enemy.');
    }

    private function draft(): array
    {
        $defaults = $this->globalDefaults();
        unset($defaults['mirror']);

        return $this->perHero('global-draft', 'Draft Stats', '/Global/Draft/', GlobalDraftController::class, 'getDraftData', $defaults, 'Pick and ban rates by draft position, and how teams do from each position.');
    }

    private function talents(): array
    {
        $chunks = [];
        $defaults = $this->globalDefaults() + ['statfilter' => 'win_rate'];
        $buildType = $this->globalDataService->getDefaultBuildType();

        foreach ($this->heroes() as $hero) {
            $id = 'global-talents-'.$hero->id;
            $details = $this->global(GlobalTalentStatsController::class, 'getGlobalHeroTalentData', $defaults + ['hero' => $hero->name]);
            $builds = $this->global(GlobalTalentStatsController::class, 'getGlobalHeroTalentBuildData', $defaults + ['hero' => $hero->name, 'talentbuildtype' => $buildType]);

            $chunks[] = $details === null || $builds === null
                ? $this->pending($id)
                : $this->chunk($id, $hero->name.' Talents & Builds', '/Global/Talents/'.rawurlencode($hero->name), $this->globalFilters().', '.$buildType.' builds', [
                    'Talent win rates and popularity by tier:',
                    $this->json($details),
                    'Top builds:',
                    $this->json($builds),
                ], $hero->name);
        }

        return $chunks;
    }

    private function heroPages(): array
    {
        $service = app(HeroPageService::class);

        return $this->heroes()->map(function ($hero) use ($service) {
            $data = $service->getHero($hero);
            $abilities = collect($data['abilities'] ?? [])->map(fn ($ability) => trim(sprintf(
                '%s%s: %s%s%s',
                data_get($ability, 'title'),
                data_get($ability, 'hotkey') ? ' ('.data_get($ability, 'hotkey').')' : '',
                strip_tags((string) data_get($ability, 'short_description')),
                data_get($ability, 'cooldown') ? ' Cooldown '.data_get($ability, 'cooldown').'.' : '',
                data_get($ability, 'mana_cost') ? ' Mana '.data_get($ability, 'mana_cost').'.' : ''
            )));
            $talents = collect($data['talents'] ?? [])->map(fn ($talent) => 'Level '.data_get($talent, 'level').' '.data_get($talent, 'title').': '.strip_tags((string) data_get($talent, 'short_description')));

            return $this->chunk('hero-page-'.$hero->id, $hero->name.' Hero Page', '/Heroes/'.rawurlencode($hero->name), 'Current patch game data', [
                $hero->name.($data['metadata']->title ?? null ? ', '.$data['metadata']->title : '').', '.$hero->new_role.'.',
                'Abilities:',
                ...$abilities,
                'Talents:',
                ...$talents,
                'The page also has full tooltips scaled to any level from 1 to 30, base stats, and a Global Stats Summary tab.',
            ], $hero->name);
        })->all();
    }

    private function player(): array
    {
        $profile = FaqMatcher::EXAMPLE_PROFILE;
        $base = self::SAMPLE_PLAYER + [
            'game_type' => $this->globalDataService->getGameTypeDefault('multi'),
            'season' => $this->globalDataService->getDefaultSeason(),
        ];
        $pages = [
            ['player-profile', 'Profile', '', PlayerController::class, 'getPlayerData', []],
            ['player-heroes', 'Heroes', '/Hero', PlayerHeroesMapsRolesController::class, 'getData', ['type' => 'all', 'page' => 'hero', 'minimumgames' => 0]],
            ['player-hero', 'Single Hero', '/Hero/'.self::SAMPLE_HERO, PlayerHeroesMapsRolesController::class, 'getData', ['type' => 'single', 'page' => 'hero', 'hero' => self::SAMPLE_HERO]],
            ['player-maps', 'Maps', '/Map', PlayerHeroesMapsRolesController::class, 'getData', ['type' => 'all', 'page' => 'map', 'minimumgames' => 0]],
            ['player-map', 'Single Map', '/Map/'.self::SAMPLE_MAP, PlayerHeroesMapsRolesController::class, 'getData', ['type' => 'single', 'page' => 'map', 'game_map' => self::SAMPLE_MAP]],
            ['player-roles', 'Roles', '/Role', PlayerHeroesMapsRolesController::class, 'getData', ['type' => 'all', 'page' => 'role', 'minimumgames' => 0]],
            ['player-role', 'Single Role', '/Role/'.self::SAMPLE_ROLE, PlayerHeroesMapsRolesController::class, 'getData', ['type' => 'single', 'page' => 'role', 'role' => self::SAMPLE_ROLE]],
            ['player-talents', 'Hero Talents', '/Talents/'.self::SAMPLE_HERO, PlayerTalentsController::class, 'getPlayerTalentData', ['hero' => self::SAMPLE_HERO]],
            ['player-matchups', 'Matchups', '/Matchups', PlayerMatchupsController::class, 'getMatchupData', []],
            ['player-friends', 'Friends & Foes (friends)', '/FriendFoe', FriendFoeController::class, 'getFriendFoeData', ['type' => 'friend']],
            ['player-foes', 'Friends & Foes (foes)', '/FriendFoe', FriendFoeController::class, 'getFriendFoeData', ['type' => 'enemy']],
            ['player-party', 'Party', '/Party', PlayerPartyController::class, 'getPartyData', []],
            ['player-mmr', 'MMR', '/MMR', PlayerMMRController::class, 'getData', ['type' => 'Player']],
            ['player-awards', 'Awards', '/Awards', PlayerAwardsController::class, 'getData', []],
            ['player-match-history', 'Match History', '/Match/History', PlayerMatchHistory::class, 'getData', ['pagination_page' => 1]],
        ];

        return collect($pages)->map(function ($page) use ($base, $profile) {
            [$id, $title, $path, $controller, $method, $parameters] = $page;
            $data = $this->call($controller, $method, $parameters + $base);
            if ($data === null || isset($data['status']) || isset($data['errors'])) {
                return $this->pending($id);
            }

            return $this->chunk($id, 'Player '.$title, '{profile}'.$path, 'Current season, '.$this->gameTypeName($base['game_type']), [
                self::SAMPLE_NOTE.' Link it as {profile}'.$path.' (example: '.$profile.$path.').',
                $this->json($data),
            ]);
        })->all();
    }

    private function activity(): array
    {
        $result = $this->call(ActivityGraphsController::class, 'getUniquePlayersPerMonth', []);
        if ($result === null || isset($result['errors'])) {
            return [$this->pending('tools-activity')];
        }

        $lines = collect($result)->take(-self::ACTIVITY_MONTHS)
            ->map(fn ($month) => $month['x_label'].': '.number_format($month['unique_players']).' unique players');

        return [$this->chunk('tools-activity', 'Activity Graphs', '/Tools/Activity', 'All game types, all regions', [
            'Unique players per month in uploaded replays, last '.self::ACTIVITY_MONTHS.' months. The current month is still counting.',
            ...$lines,
        ])];
    }

    private function xalatathRecap(): array
    {
        if (! $this->globalDataService->isXalatathRecapAvailable()) {
            return [$this->pending('event-xalatath')];
        }

        $final = $this->globalDataService->getXalatathFinalState();
        $totals = $final['totals'] ?? [];
        $stat = fn ($column) => number_format((float) ($totals[$column] ?? 0));
        $hours = fn ($column) => number_format(floor(($totals[$column] ?? 0) / 3600));

        $startsAt = Carbon::parse(config('global.xalatath_event.starts_at'));
        $endsAt = $this->globalDataService->getXalatathEventEndsAt();
        $eyeHunt = app(XalatathRecapController::class)->eyeHunt($startsAt);
        $busiest = collect($eyeHunt['per_day'])->sortDesc()->take(1);

        return [$this->chunk('event-xalatath', 'Xal\'atath Event Recap', '/Event/Xalatath', 'Final totals; the event has ended', [
            'Xal\'atath took over Heroes Profile from '.$startsAt->toDateString().' to '.($endsAt?->toDateString() ?? 'the end').'. Final stage '.($final['stage'] ?? 0).'.',
            'Mark of the Void: '.number_format($eyeHunt['finders']).' players found Xal\'atath\'s eye and earned the Mark of the Void, an eye shown beside their battletag across the site for good.'
                .($busiest->isNotEmpty() ? ' Busiest day: '.number_format($busiest->first()).' on '.$busiest->keys()->first().'.' : ''),
            'Minds Consumed (takedowns): '.$stat('takedowns'),
            'Void Damage Unleashed (spell damage): '.$stat('spell_damage'),
            'Realms Shattered (siege damage / 12,900): '.number_format(floor(($totals['siege_damage'] ?? 0) / 12900)),
            'Mass Consumptions (multikills): '.$stat('multikill'),
            'Banished to the Void (deaths): '.$stat('deaths'),
            'Sealed Away (bans): '.$stat('bans'),
            'Cold Feet (escapes): '.$stat('escapes'),
            'Games Played: '.$stat('games_played'),
            'Victories: '.$stat('wins'),
            'Void Unleashed in Battle (team fight hero damage): '.$stat('teamfight_hero_damage'),
            'Hours Ablaze (time on fire): '.$hours('on_fire_time'),
            'Longest Rampage (kill streak): '.$stat('highest_kill_streak'),
            'Outnumbered & Undone (deaths while outnumbered): '.$stat('outnumbered_deaths'),
            'Hours Sealed in the Dagger (time dead): '.$hours('time_spent_dead'),
        ])];
    }

    private function headings(): array
    {
        return collect(self::HEADINGS)->map(fn ($heading, $url) => $this->chunk(
            'page-'.trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($url)), '-'),
            $heading[0],
            $url,
            null,
            array_filter([$heading[1], $this->description($url)])
        ))->values()->all();
    }

    // One cached global query per hero, the request the page sends once a hero is picked
    private function perHero(string $prefix, string $title, string $path, string $controller, string $method, array $defaults, string $about): array
    {
        return $this->heroes()->map(function ($hero) use ($prefix, $title, $path, $controller, $method, $defaults, $about) {
            $id = $prefix.'-'.$hero->id;
            $data = $this->global($controller, $method, $defaults + ['hero' => $hero->name]);

            return $data === null
                ? $this->pending($id)
                : $this->chunk($id, $hero->name.' '.$title, $path.rawurlencode($hero->name), $this->globalFilters(), [$about, $this->json($data)], $hero->name);
        })->all();
    }

    // What the global pages send on first load, before any filter is touched
    private function globalDefaults(): array
    {
        return [
            'timeframe_type' => $this->globalDataService->getDefaultTimeframeType(),
            'timeframe' => [$this->globalDataService->getDefaultTimeframe()],
            'game_type' => $this->globalDataService->getGameTypeDefault('multi'),
            'mirror' => 0,
        ];
    }

    private function globalFilters(): string
    {
        return 'Patch '.$this->globalDataService->getDefaultTimeframe().', '.$this->gameTypeName($this->globalDataService->getGameTypeDefault('multi')).', all ranks, all regions, all maps';
    }

    private function gameTypeName(array $shortNames): string
    {
        return $this->globalDataService->getGameTypes()->whereIn('short_name', $shortNames)->pluck('name')->implode(', ');
    }

    private function heroes()
    {
        return $this->globalDataService->getHeroes();
    }

    private function description(string $url): ?string
    {
        $this->pageDescriptions ??= collect(config('faq.pages'))->pluck('items')->flatten(1)
            ->mapWithKeys(fn ($page) => [preg_replace('#^/Player/[^/]+/\d+/\d+#', '{profile}', $page['url']) => $page['description']])
            ->all();

        return $this->pageDescriptions[$url] ?? null;
    }

    // A cached async result, or null after queueing it, the same way the page would
    private function global(string $controller, string $method, array $parameters): ?array
    {
        if ($this->queued >= self::MAX_QUEUED) {
            return null;
        }

        $result = app()->call([app($controller), $method], ['request' => Request::create('/', 'POST', $parameters)]);

        if ($result instanceof JsonResponse && $result->getStatusCode() === 202) {
            $this->queued++;

            return null;
        }

        $result = $this->toArray($result);

        return $result === null || isset($result['status']) ? null : $result;
    }

    private function call(string $controller, string $method, array $parameters): ?array
    {
        return $this->toArray(app()->call([app($controller), $method], ['request' => Request::create('/', 'POST', $parameters)]));
    }

    private function toArray(mixed $result): ?array
    {
        if ($result instanceof JsonResponse) {
            return $result->getStatusCode() === 200 ? $result->getData(true) : null;
        }

        return json_decode(json_encode($result), true);
    }

    // Long lists cut to their first rows, so one table can't fill the chunk
    private function json(mixed $data): string
    {
        $trim = function ($value) use (&$trim) {
            if (! is_array($value)) {
                return $value;
            }
            if (array_is_list($value) && count($value) > self::MAX_ROWS) {
                $value = [...array_slice($value, 0, self::MAX_ROWS), '... '.(count($value) - self::MAX_ROWS).' more'];
            }

            return array_map($trim, $value);
        };

        return json_encode($trim($data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function chunk(string $id, string $title, string $url, ?string $filters, array $lines, ?string $hero = null): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'url' => $url,
            'filters' => $filters,
            'hero' => $hero,
            'text' => implode("\n", $lines),
        ];
    }

    private function pending(string $id): array
    {
        return ['id' => $id, 'pending' => true];
    }
}
