<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\BattlenetAccount;
use App\Models\BattlenetAccountFlair;
use App\Models\Battletag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class XalatathRecapController extends Controller
{
    private const HERO_ID = 92;

    private const LEADERBOARD_SIZE = 25;

    private const CARD_DISK = 'gcs-xalatath-cards';

    public function show()
    {
        if (! $this->globalDataService->isXalatathRecapAvailable()) {
            return redirect('/');
        }

        $recap = $this->globalDataService->getXalatathRecap();
        $startsAt = Carbon::parse(config('global.xalatath_event.starts_at'));
        $endsAt = $this->globalDataService->getXalatathEventEndsAt() ?? now();

        $heroIds = collect($recap['matchups'] ?? [])->pluck('hero')
            ->merge(collect($recap['launches'] ?? [])->pluck('hero'))
            ->push(self::HERO_ID)
            ->unique();

        $talentIds = collect($recap['talents'] ?? [])->pluck('talent_id')
            ->merge(collect($recap['builds'] ?? [])->pluck('talents')->flatten())
            ->merge(collect($recap['nerf_talents'] ?? [])->pluck('talent_id'))
            ->unique();

        return view('event.xalatath-recap')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'recap' => [
                'final' => $this->globalDataService->getXalatathFinalState(),
                'sections' => collect($recap)->except('leaderboard')->all(),
                'leaderboard' => $this->visibleLeaderboard($recap['leaderboard'] ?? []),
                'heroes' => $this->globalDataService->getHeroesByID()->only($heroIds->all())->keyBy('id'),
                'maps' => $this->globalDataService->getAllMapsKeyed(),
                'talents' => $this->globalDataService->getAllTalentsKeyed()->only($talentIds->all())->keyBy('talent_id'),
                'awards' => Award::get(['award_id', 'title', 'icon'])->keyBy('award_id'),
                'gameTypes' => $this->globalDataService->getGameTypes()->pluck('name', 'type_id'),
                'regions' => $this->globalDataService->getRegionIDtoString(),
                'eyeHunt' => $this->eyeHunt($startsAt),
                'startsAt' => $startsAt->toIso8601String(),
                'endsAt' => $endsAt->toIso8601String(),
            ],
            'contribution' => $this->contribution($startsAt, $endsAt),
        ]);
    }

    /**
     * Stores the card the player's browser drew, so their shared link can preview it.
     * Private accounts get no public card; the page falls back to the recap link.
     */
    public function uploadCard(Request $request)
    {
        if (! $this->globalDataService->isXalatathRecapAvailable()) {
            return response()->json(['status' => 'closed'], 410);
        }

        if (! Auth::check()) {
            return response()->json(['status' => 'login'], 401);
        }

        $request->validate([
            'card' => 'required|file|mimetypes:image/png|max:2048|dimensions:width=1200,height=675',
        ]);

        $user = Auth::user();

        if ($this->globalDataService->isHiddenFrom($user->blizz_id, $user->region, null)) {
            return response()->json(['url' => null]);
        }

        Storage::disk(self::CARD_DISK)->put($this->cardPath($user->blizz_id, $user->region), $request->file('card')->get(), [
            'metadata' => ['contentType' => 'image/png', 'cacheControl' => 'public, max-age=300'],
        ]);
        Cache::forever($this->cardVersionKey($user->blizz_id, $user->region), now()->timestamp);

        $name = explode('#', (string) $user->battletag)[0];

        return response()->json(['url' => url("/Event/Xalatath/Card/{$name}/{$user->blizz_id}/{$user->region}")]);
    }

    // The shareable page: exists so X/Bluesky have an og:image of this player's card to unfurl.
    public function showCard(string $battletag, int $blizz_id, int $region)
    {
        if (! $this->globalDataService->isXalatathRecapAvailable()
            || $this->globalDataService->isHiddenFrom($blizz_id, $region, Auth::user())) {
            return redirect('/Event/Xalatath');
        }

        $version = $this->cardVersion($blizz_id, $region);

        if ($version === null) {
            return redirect('/Event/Xalatath');
        }

        // The name shown comes from our records, not the URL.
        $name = explode('#', (string) Battletag::where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->orderByDesc('latest_game')
            ->value('battletag'))[0] ?: $battletag;

        $bucket = config('filesystems.disks.'.self::CARD_DISK.'.bucket');

        return view('event.xalatath-card')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'name' => $name,
            'imageUrl' => "https://storage.googleapis.com/{$bucket}/".$this->cardPath($blizz_id, $region)."?v={$version}",
        ]);
    }

    private function cardPath($blizzId, $region): string
    {
        return "cards/{$blizzId}-{$region}.png";
    }

    private function cardVersionKey($blizzId, $region): string
    {
        return "xalatath_card_version:{$blizzId}|{$region}";
    }

    // Upload time, used to bust preview caches; null when this player never shared.
    private function cardVersion($blizzId, $region): ?int
    {
        $key = $this->cardVersionKey($blizzId, $region);
        $version = Cache::get($key);

        if ($version !== null) {
            return (int) $version;
        }

        $disk = Storage::disk(self::CARD_DISK);
        $path = $this->cardPath($blizzId, $region);

        if (! $disk->exists($path)) {
            return null;
        }

        $version = $disk->lastModified($path);
        Cache::forever($key, $version);

        return $version;
    }

    // Private and banned players are dropped for this viewer, ranks closed up.
    private function visibleLeaderboard(array $rows): array
    {
        $viewer = Auth::user();

        // Same rule as checkIfSiteFlair: only Patreon accounts with site flair enabled.
        $patreonAccounts = BattlenetAccount::without(['patreonAccount', 'userSettings'])
            ->whereHas('patreonAccount', fn ($query) => $query->where('site_flair', 1))
            ->get(['blizz_id', 'region'])
            ->keyBy(fn ($a) => $a->blizz_id.'|'.$a->region);

        // One list per game mode (game_type null = all modes), each trimmed to its own top 25.
        return collect($rows)
            ->groupBy(fn ($row) => $row['game_type'] ?? 'all')
            ->flatMap(fn ($group) => $group
                ->sortBy('rank')
                ->reject(fn ($row) => $this->globalDataService->isHiddenFrom($row['blizz_id'], $row['region'], $viewer))
                ->take(self::LEADERBOARD_SIZE)
                ->values()
                ->map(fn ($row, $i) => [...$row, 'rank' => $i + 1]))
            ->map(fn ($row) => [
                'game_type' => $row['game_type'] ?? null,
                'rank' => $row['rank'],
                'blizz_id' => $row['blizz_id'],
                'region' => $row['region'],
                'battletag' => explode('#', $row['battletag'])[0],
                'games' => $row['games'],
                'wins' => $row['wins'],
                'patreon' => $patreonAccounts->has($row['blizz_id'].'|'.$row['region'])
                    && ! $this->globalDataService->isFlairHidden('patreon', $row['blizz_id'], $row['region']),
                'hp_owner' => $this->globalDataService->showOwnerFlair($row['blizz_id'], $row['region']),
            ])
            ->all();
    }

    private function eyeHunt(Carbon $startsAt): array
    {
        return $this->globalDataService->rememberXalatathRecap('xalatath_recap_eye_hunt', function () use ($startsAt) {
            // Rows before launch are test claims from development.
            $awards = BattlenetAccountFlair::where('flair', BattlenetAccountFlair::XALATATH_EYE)
                ->where('awarded_at', '>=', $startsAt)
                ->orderBy('awarded_at')
                ->pluck('awarded_at');

            return [
                'finders' => $awards->count(),
                'per_day' => $awards->countBy(fn ($awardedAt) => $awardedAt->toDateString())->all(),
            ];
        });
    }

    private function contribution(Carbon $startsAt, Carbon $endsAt): ?array
    {
        if (! Auth::check()) {
            return null;
        }

        $user = Auth::user();

        return $this->globalDataService->rememberXalatathRecap("xalatath_recap_contribution_v2:{$user->blizz_id}|{$user->region}", function () use ($user, $startsAt, $endsAt) {
            // This player's Xal'atath games in the event window; player(blizz_id, hero) keeps it cheap.
            $games = fn () => DB::connection('heroesprofile')->table('player')
                ->join('replay', 'replay.replayID', '=', 'player.replayID')
                ->where('player.blizz_id', $user->blizz_id)
                ->where('player.hero', self::HERO_ID)
                ->where('replay.region', $user->region)
                ->where('replay.game_type', '<>', 0)
                ->where('replay.game_date', '>=', $startsAt)
                ->where('replay.game_date', '<', $endsAt);

            $row = $games()
                ->selectRaw('COUNT(*) AS games, COALESCE(SUM(player.winner), 0) AS wins, MIN(replay.game_date) AS first_game')
                ->first();

            $contribution = [
                'battletag' => explode('#', (string) $user->battletag)[0],
                'games' => (int) $row->games,
                'wins' => (int) $row->wins,
                'first_game' => $row->first_game,
                'modes' => [],
                'build' => null,
                'allies' => [],
                'enemies' => [],
            ];

            if ($contribution['games'] === 0) {
                return $contribution;
            }

            $gameTypes = $this->globalDataService->getGameTypes()->pluck('name', 'type_id');
            $contribution['modes'] = $games()
                ->groupBy('replay.game_type')
                ->selectRaw('replay.game_type, COUNT(*) AS games, SUM(player.winner) AS wins')
                ->orderByDesc('games')
                ->get()
                ->map(fn ($mode) => [
                    'name' => $gameTypes[$mode->game_type] ?? (string) $mode->game_type,
                    'games' => (int) $mode->games,
                    'wins' => (int) $mode->wins,
                ])
                ->all();

            $levels = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];
            $buildQuery = $games()->join('talents', function ($join) {
                $join->on('talents.replayID', '=', 'player.replayID')
                    ->on('talents.battletag', '=', 'player.battletag');
            });
            foreach ($levels as $level) {
                $buildQuery->where("talents.{$level}", '>', 0);
            }
            $build = $buildQuery
                ->groupBy(array_map(fn ($level) => "talents.{$level}", $levels))
                ->selectRaw(implode(', ', array_map(fn ($level) => "talents.{$level}", $levels)).', COUNT(*) AS games, SUM(player.winner) AS wins')
                ->orderByDesc('games')
                ->first();

            if ($build) {
                $talents = $this->globalDataService->getAllTalentsKeyed();
                $contribution['build'] = [
                    'talents' => array_map(fn ($level) => $talents->get($build->{$level}), $levels),
                    'games' => (int) $build->games,
                    'wins' => (int) $build->wins,
                ];
            }

            // Most common heroes beside and against them, with their win rate in those games.
            $heroes = $this->globalDataService->getHeroesByID();
            $mates = $games()
                ->join('player as other', function ($join) {
                    $join->on('other.replayID', '=', 'player.replayID')
                        ->on('other.battletag', '<>', 'player.battletag');
                })
                ->where('other.hero', '<>', self::HERO_ID)
                ->groupByRaw('relation, other.hero')
                ->selectRaw("IF(other.team = player.team, 'ally', 'enemy') AS relation, other.hero, COUNT(*) AS games, SUM(player.winner) AS wins")
                ->get();

            foreach (['ally' => 'allies', 'enemy' => 'enemies'] as $relation => $key) {
                $contribution[$key] = $mates->where('relation', $relation)
                    ->sortByDesc('games')
                    ->take(3)
                    ->map(fn ($mate) => [
                        'hero' => $heroes->get($mate->hero),
                        'games' => (int) $mate->games,
                        'wins' => (int) $mate->wins,
                    ])
                    ->values()
                    ->all();
            }

            return $contribution;
        });
    }
}
