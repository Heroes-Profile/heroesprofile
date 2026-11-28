<?php

namespace Tests\Feature\Backend;

use App\Models\BattlenetAccount;
use App\Models\MatchPredictionPlayerStat;
use App\Models\MatchPredictionSeason;
use App\Services\GlobalDataService;
use Tests\Feature\Characterization\SeedFixtures;
use Tests\Feature\FeatureTestCase;

/** A filter changes the rows: every row that comes back matches what was picked. */
class FilterBehaviourTest extends FeatureTestCase
{
    use SeedFixtures;

    public function test_match_prediction_leaderboard_keeps_to_the_chosen_region(): void
    {
        $season = MatchPredictionSeason::max('match_prediction_season_id');
        $gameType = app(GlobalDataService::class)->getGameTypeFilterValues('sl');

        // One predictor in NA and one in EU, both past the most games any group asks for
        foreach ([[900101, 1], [900102, 2]] as [$id, $region]) {
            BattlenetAccount::forceCreate([
                'battlenet_accounts_id' => $id, 'battlenet_id' => $id, 'battletag' => "Predictor{$region}#{$id}",
                'blizz_id' => $id, 'region' => $region,
            ]);
            MatchPredictionPlayerStat::forceCreate([
                'battlenet_accounts_id' => $id, 'season' => $season, 'game_type' => $gameType, 'win' => 30, 'loss' => 10,
            ]);
        }

        $payload = ['type' => 'match prediction', 'season' => $season, 'game_type' => 'sl', 'groupsize' => 'All'];

        $na = collect($this->postJson('/api/v1/global/leaderboard', $payload + ['region' => 'NA'])->assertOk()->json());
        $this->assertTrue($na->contains('blizz_id', 900101), 'the NA predictor is listed');
        $this->assertSame(['NA'], $na->pluck('region')->unique()->values()->all());

        $eu = collect($this->postJson('/api/v1/global/leaderboard', $payload + ['region' => 'EU'])->assertOk()->json());
        $this->assertTrue($eu->contains('blizz_id', 900102), 'the EU predictor is listed');
        $this->assertSame(['EU'], $eu->pluck('region')->unique()->values()->all());

        $all = collect($this->postJson('/api/v1/global/leaderboard', $payload)->assertOk()->json());
        $this->assertTrue($all->contains('blizz_id', 900101) && $all->contains('blizz_id', 900102), 'no region lists both');
    }

    public function test_global_hero_stats_keep_to_the_chosen_role(): void
    {
        $rows = collect($this->postJson('/api/v1/global/hero', [
            'timeframe_type' => 'minor',
            'timeframe' => [self::GAME_VERSION],
            'game_type' => ['sl'],
            'statfilter' => 'win_rate',
            'role' => 'Tank',
            'mirror' => 0,
        ])->assertOk()->json('data'));

        $this->assertNotEmpty($rows, 'the seeded patch has tanks');
        $this->assertSame(['Tank'], $rows->pluck('role')->unique()->values()->all());
    }

    public function test_ccl_organizations_keep_to_the_chosen_season(): void
    {
        $rows = collect($this->postJson('/api/v1/esports/ccl/organizations', ['season' => self::CCL_SEASON])->assertOk()->json());

        $this->assertNotEmpty($rows);
        $this->assertSame([self::CCL_SEASON], $rows->pluck('season')->unique()->values()->all());
    }

    /** The organizations the CCL page's dropdown narrows by season carry their season */
    public function test_ccl_page_organizations_carry_their_season(): void
    {
        $organizations = $this->get('/Esports/CCL')->assertOk()->viewData('organizations');

        $this->assertNotEmpty($organizations);
        $this->assertTrue(collect($organizations)->every(fn ($row) => isset($row['season'])));
        $this->assertTrue(collect($organizations)->contains(fn ($row) => $row['season'] == self::CCL_SEASON && $row['code'] === self::CCL_TEAM));
    }

    public function test_other_matches_keep_to_the_chosen_series(): void
    {
        $rows = collect($this->postJson('/api/v1/esports/other/get/all/matches', [
            'series' => self::OTHER_SERIES_ID,
            'pagination_page' => 1,
        ])->assertOk()->json('data'));

        $this->assertNotEmpty($rows);
        $this->assertSame([self::OTHER_SERIES], $rows->pluck('series')->unique()->values()->all());
    }

    /** The Other page's lists carry their series, so picking one narrows the rest */
    public function test_other_page_lists_carry_their_series(): void
    {
        $response = $this->get('/Esports/Other')->assertOk();

        foreach (['seasons', 'regions', 'tournaments', 'teams'] as $list) {
            $rows = collect($response->viewData($list))->map(fn ($row) => (array) (is_object($row) && method_exists($row, 'toArray') ? $row->toArray() : $row));
            $this->assertNotEmpty($rows, "{$list} is empty");
            $this->assertTrue($rows->every(fn ($row) => ! empty($row['series'])), "every {$list} option names its series");
            $this->assertTrue($rows->contains('series', self::OTHER_SERIES), "{$list} has options for ".self::OTHER_SERIES);
        }
    }
}
