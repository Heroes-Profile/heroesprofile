<?php

namespace Tests\Feature\Backend;

use App\Http\Middleware\ThrottleNonApiRequests;
use App\Models\CCL\CCLTeam;
use App\Models\HeroesInternational\HeroesInternationalMainTeam;
use App\Models\HeroesInternational\HeroesInternationalNationsCupTeam;
use App\Models\MastersClash\MastersClashTeam;
use App\Models\NGS\NGSTeam;
use App\Services\GlobalDataService;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Characterization\SeedFixtures;
use Tests\Feature\FeatureTestCase;

/**
 * Every option the filter bar can offer is accepted by the endpoints it posts to. Each case changes
 * one field of a payload that passes, so a rejection names the option the server doesn't take.
 */
class FilterOptionsValidateTest extends FeatureTestCase
{
    use SeedFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        // Hundreds of requests from one client; the rate limits aren't what's under test
        $this->withoutMiddleware([ThrottleRequests::class, ThrottleNonApiRequests::class]);
    }

    private const ALL_GAME_TYPES = ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'];

    private static function globalFilters(array $extra = []): array
    {
        return $extra + [
            'timeframe_type' => 'minor',
            'timeframe' => [self::GAME_VERSION],
            'game_type' => ['sl'],
            'region' => null,
            'statfilter' => 'win_rate',
            'hero_level' => null,
            'role' => null,
            'game_map' => null,
            'league_tier' => null,
            'hero_league_tier' => null,
            'role_league_tier' => null,
            'mirror' => 0,
        ];
    }

    private static function player(array $extra = []): array
    {
        return $extra + [
            'battletag' => self::PLAYER_BATTLETAG,
            'blizz_id' => self::PLAYER_BLIZZ_ID,
            'region' => self::PLAYER_REGION,
            'game_type' => self::ALL_GAME_TYPES,
            'season' => null,
        ];
    }

    /** Filters every global stats page has */
    private const GLOBAL_FIELDS = ['timeframe', 'region', 'game_type', 'game_map', 'hero_level', 'league_tier', 'hero_league_tier', 'role_league_tier', 'mirror'];

    public static function globalEndpoints(): array
    {
        $global = fn (array $extra = []) => self::globalFilters($extra);

        return [
            'global hero' => ['global/hero', $global(), [...self::GLOBAL_FIELDS, 'role', 'hero', 'statfilter']],
            'global talents' => ['global/talents', $global(['hero' => self::HERO]), [...self::GLOBAL_FIELDS, 'statfilter']],
            'global talents build' => ['global/talents/build', $global(['hero' => self::HERO, 'talentbuildtype' => 'Popular']), [...self::GLOBAL_FIELDS, 'talentbuildtype']],
            'global talents builder' => ['global/talents/builder', $global(['hero' => self::HERO, 'selectedtalents' => []]), self::GLOBAL_FIELDS],
            'global hero map' => ['global/hero/map', $global(['hero' => self::HERO]), self::GLOBAL_FIELDS],
            'global matchups' => ['global/matchups', $global(['hero' => self::HERO]), [...self::GLOBAL_FIELDS, 'role']],
            'global compositions' => ['global/compositions', $global(['minimum_games' => 0]), [...self::GLOBAL_FIELDS, 'hero', 'minimum_games']],
            'global draft' => ['global/draft', $global(['hero' => self::HERO]), self::GLOBAL_FIELDS],
            'global party' => ['global/party', $global(['teamoneparty' => '00005', 'teamtwoparty' => '00005']), [...self::GLOBAL_FIELDS, 'hero', 'teamoneparty', 'teamtwoparty']],
            'drafter draft order' => ['drafter/draft-order', $global(), self::GLOBAL_FIELDS],
            'drafter composition' => ['drafter/composition', $global(), self::GLOBAL_FIELDS],
        ];
    }

    #[DataProvider('globalEndpoints')]
    public function test_every_global_filter_option_validates(string $uri, array $base, array $fields): void
    {
        $this->assertEveryVariantValidates($uri, $base, $this->globalVariants($fields));
    }

    public function test_every_leaderboard_filter_option_validates(): void
    {
        $filters = $this->filters();
        $base = ['season' => 26, 'game_type' => 'sl', 'type' => 'player', 'groupsize' => 'All'];

        $variants = [];
        foreach (['player', 'hero', 'role', 'map', 'match prediction', 'sl rank'] as $type) {
            $variants["type {$type}"] = ['type' => $type] + match ($type) {
                'hero' => ['hero' => self::PLAYER_HERO_ID],
                'role' => ['role' => 'Healer'],
                'map' => ['map' => self::MAP],
                default => [],
            };
        }
        foreach ($this->codes($filters->seasons) as $season) {
            $variants["season {$season}"] = ['season' => $season];
        }
        foreach ($this->codes($filters->match_prediction_seasons) as $season) {
            $variants["match prediction season {$season}"] = ['type' => 'match prediction', 'season' => $season];
        }
        foreach ($this->codes($filters->game_types) as $gameType) {
            $variants["game type {$gameType}"] = ['game_type' => $gameType];
        }
        foreach ($this->codes($filters->group_size) as $size) {
            $variants["group size {$size}"] = ['groupsize' => $size];
        }
        foreach ($this->codes($filters->regions) as $region) {
            $variants["region {$region}"] = ['region' => $region];
            $variants["match prediction region {$region}"] = ['type' => 'match prediction', 'region' => $region];
        }
        foreach ($this->codes($filters->heroes) as $hero) {
            $variants["hero {$hero}"] = ['type' => 'hero', 'hero' => $hero];
        }
        foreach ($this->codes($filters->role) as $role) {
            $variants["role {$role}"] = ['type' => 'role', 'role' => $role];
        }
        foreach ($this->codes($filters->game_maps) as $map) {
            $variants["map {$map}"] = ['type' => 'map', 'map' => $map];
        }
        foreach ($this->codes($filters->rank_tiers) as $tier) {
            $variants["tier {$tier}"] = ['tierrank' => $tier];
        }
        foreach ($this->codes($filters->sl_leagues) as $league) {
            $variants["sl league {$league}"] = ['type' => 'sl rank', 'league' => $league];
        }

        $this->assertEveryVariantValidates('global/leaderboard', $base, $variants);
    }

    public static function playerEndpoints(): array
    {
        $player = fn (array $extra = []) => self::player($extra);

        return [
            'player' => ['player', $player()],
            'player heroes all' => ['player/heroes/all', $player(['type' => 'all', 'page' => 'hero', 'minimumgames' => 0])],
            'player roles all' => ['player/roles/all', $player(['type' => 'all', 'page' => 'role', 'minimumgames' => 0])],
            'player maps all' => ['player/maps/all', $player(['type' => 'all', 'page' => 'map', 'minimumgames' => 0])],
            'player matchups' => ['player/matchups', $player()],
            'player awards' => ['player/awards', $player()],
            'player match history' => ['player/match/history', $player(['pagination_page' => 1])],
        ];
    }

    #[DataProvider('playerEndpoints')]
    public function test_every_player_filter_option_validates(string $uri, array $base): void
    {
        $filters = $this->filters();

        $variants = [];
        foreach ($this->codes($filters->game_types) as $gameType) {
            $variants["game type {$gameType}"] = ['game_type' => [$gameType]];
        }
        foreach ($this->codes($filters->seasons) as $season) {
            $variants["season {$season}"] = ['season' => [$season]];
        }

        $this->assertEveryVariantValidates($uri, $base, $variants);
    }

    /** Each league's endpoints, the season list its page offers, and the payload around the season */
    public static function leagueSeasons(): array
    {
        return [
            'ngs teams' => ['esports/ngs/teams', 'ngs_seasons', []],
            'ngs matches' => ['esports/ngs/matches', 'ngs_seasons', ['esport' => 'NGS', 'pagination_page' => 1]],
            'ngs hero stats' => ['esports/ngs/hero/stats', 'ngs_seasons', ['esport' => 'NGS']],
            'ccl organizations' => ['esports/ccl/organizations', 'ccl_seasons', []],
            'ccl matches' => ['esports/ccl/matches', 'ccl_seasons', ['esport' => 'CCL', 'pagination_page' => 1]],
            'ccl hero stats' => ['esports/ccl/hero/stats', 'ccl_seasons', ['esport' => 'CCL']],
            'mcl teams' => ['esports/mastersclash/teams', 'mcl_seasons', []],
            'mcl hero stats' => ['esports/mastersclash/hero/stats', 'mcl_seasons', ['esport' => 'MastersClash']],
            'hi teams' => ['esports/heroesinternational/teams', 'hi_seasons', ['esport' => 'hi']],
            'hi hero stats' => ['esports/heroesinternational/hero/stats', 'hi_seasons', ['esport' => 'hi', 'tournament' => 'main']],
            'hi nations cup teams' => ['esports/heroesinternational/teams', 'hi_nc_seasons', ['esport' => 'hi_nc']],
            'hi nations cup hero stats' => ['esports/heroesinternational/hero/stats', 'hi_nc_seasons', ['esport' => 'hi_nc', 'tournament' => 'nationscup']],
            'nutcup hero stats' => ['esports/nutcup/hero/stats', 'nut_cup_seasons', ['esport' => 'NutCup']],
        ];
    }

    #[DataProvider('leagueSeasons')]
    public function test_every_league_season_validates(string $uri, string $list, array $base): void
    {
        $variants = [];
        foreach ($this->codes($this->filters()->{$list}) as $season) {
            $variants["season {$season}"] = ['season' => $season];
        }

        $this->assertNotEmpty($variants, "{$list} is empty");
        $this->assertEveryVariantValidates($uri, $base, $variants);
    }

    /** The team-list endpoints and the table their season rule reads */
    public static function leagueTeamTables(): array
    {
        return [
            'ngs' => ['esports/ngs/teams', NGSTeam::class, []],
            'ccl' => ['esports/ccl/organizations', CCLTeam::class, []],
            'mcl' => ['esports/mastersclash/teams', MastersClashTeam::class, []],
            'hi' => ['esports/heroesinternational/teams', HeroesInternationalMainTeam::class, ['esport' => 'hi']],
            'hi nations cup' => ['esports/heroesinternational/teams', HeroesInternationalNationsCupTeam::class, ['esport' => 'hi_nc']],
        ];
    }

    /** A new season works as soon as its teams exist, with no list in the code to update */
    #[DataProvider('leagueTeamTables')]
    public function test_a_new_league_season_validates(string $uri, string $model, array $base): void
    {
        $latest = $model::query()->orderByDesc('season')->first();
        $this->assertNotNull($latest, "{$model} has no seeded teams");

        $row = $latest->getAttributes();
        $key = $latest->getKeyName();
        $row[$key] = $model::query()->max($key) + 1;
        $row['season'] = (int) $latest->season + 1;
        $model::query()->insert($row);

        $this->assertEveryVariantValidates($uri, $base, ['next season' => ['season' => $row['season']]]);
    }

    /** Field name => each change the filter bar can make to it */
    private function globalVariants(array $fields): array
    {
        $filters = $this->filters();
        $variants = [];

        foreach ($fields as $field) {
            switch ($field) {
                case 'timeframe':
                    $lists = ['minor' => $filters->timeframes, 'major' => $filters->timeframes_grouped, 'major_grouped' => $filters->timeframes_sub_grouped];
                    foreach ($lists as $type => $list) {
                        foreach ($this->sample($this->codes($list)) as $code) {
                            $variants["timeframe {$type} {$code}"] = ['timeframe_type' => $type, 'timeframe' => [$code]];
                        }
                    }
                    break;
                case 'hero':
                    foreach ($this->names($filters->heroes) as $hero) {
                        $variants["hero {$hero}"] = ['hero' => $hero];
                    }
                    break;
                default:
                    [$list, $multi] = match ($field) {
                        'region' => [$filters->regions, true],
                        'game_type' => [$filters->game_types, true],
                        'game_map' => [$filters->game_maps, true],
                        'hero_level' => [$filters->hero_level, true],
                        'league_tier', 'hero_league_tier', 'role_league_tier' => [$filters->rank_tiers, true],
                        'role' => [$filters->role, false],
                        'statfilter' => [$filters->stat_filter, false],
                        'mirror' => [$filters->mirror, false],
                        'minimum_games' => [$filters->minimum_games, false],
                        'teamoneparty', 'teamtwoparty' => [$filters->party_combinations, false],
                        'talentbuildtype' => [$filters->talent_build_types, false],
                    };
                    foreach ($this->codes($list) as $code) {
                        $variants["{$field} {$code}"] = [$field => $multi ? [$code] : $code];
                    }
            }
        }

        return $variants;
    }

    private function assertEveryVariantValidates(string $uri, array $base, array $variants): void
    {
        $failures = [];
        foreach ($variants as $label => $change) {
            $response = $this->postJson('/api/v1/'.$uri, array_merge($base, $change));
            if ($problem = $this->rejection($response)) {
                $failures[] = "{$label}: {$problem}";
            }
        }

        $this->assertSame([], $failures, "{$uri} rejected options the filter bar offers");
    }

    private function rejection(TestResponse $response): ?string
    {
        if ($response->status() >= 400) {
            return 'HTTP '.$response->status();
        }

        $json = json_decode($response->getContent(), true);
        if (is_array($json) && ($json['status'] ?? null) === 'failure to validate inputs') {
            return json_encode($json['errors'] ?? $json);
        }

        return null;
    }

    private function filters(): object
    {
        return app(GlobalDataService::class)->getFilterData();
    }

    private function codes(iterable $list): array
    {
        return collect($list)->pluck('code')->unique()->values()->all();
    }

    private function names(iterable $list): array
    {
        return collect($list)->pluck('name')->unique()->values()->all();
    }

    /** The newest few and the oldest: patches are many and all go through the same rule */
    private function sample(array $codes): array
    {
        return array_values(array_unique([...array_slice($codes, 0, 5), ...array_slice($codes, -1)]));
    }
}
