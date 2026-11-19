<?php

namespace Tests\Feature\Backend;

use App\Models\Api\ApiAccount;
use App\Models\BattlenetAccount;
use App\Services\AnalyticsQueryService;
use App\Services\AnalyticsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Characterization\AssertsSnapshots;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FakeBigQuery;
use Tests\Support\SimulatesCloudTasks;

/**
 * Player Analytics with BigQuery and the Deep Analytics parser simulated: who gets in, the
 * request → fan-out → parse pipeline, and every data tab's queries and post-processing.
 */
class AnalyticsTest extends FeatureTestCase
{
    use AssertsSnapshots;
    use SimulatesCloudTasks;

    private const BLIZZ_ID = 900010;

    private const REGION = 1;

    /** Season games (after analytics.cutover): loaded at the latest version, loaded at an older one, archived, uploaded */
    private const CURRENT = 99000001;

    private const OUTDATED = 99000002;

    private const ARCHIVED = 99000003;

    private const UPLOADED = 99000004;

    private FakeBigQuery $bigQuery;

    protected function setUp(): void
    {
        parent::setUp();

        // Analytics only counts games after the 2026-10-02 cutover
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00', 'UTC'));

        $this->setUpCloudTasks();
        config(['analytics.allowlist' => [67280], 'analytics.service_url' => 'https://deep-analytics.test/parse']);

        $this->bigQuery = new FakeBigQuery(app(AnalyticsService::class));
        $this->app->instance(AnalyticsQueryService::class, $this->bigQuery);

        $this->seedSeasonGames();
    }

    private function seedSeasonGames(): void
    {
        $db = DB::connection('heroesprofile');

        foreach ([self::CURRENT, self::OUTDATED, self::ARCHIVED, self::UPLOADED] as $i => $replayId) {
            $db->table('replay')->insert([
                'replayID' => $replayId, 'game_type' => 5, 'game_date' => Carbon::parse('2026-10-10 18:00:00')->addHours($i),
                'game_length' => 1200, 'game_map' => 1, 'game_version' => '2.55.12.94786', 'region' => self::REGION,
                'date_added' => '2026-10-11 00:00:00',
            ]);
            $db->table('player')->insert([
                'replayID' => $replayId, 'blizz_id' => self::BLIZZ_ID, 'battletag' => 1, 'hero' => 80,
                'hero_level' => 20, 'team' => 0, 'winner' => $i % 2,
            ]);
        }

        $db->table('replay_deep')->insert([
            ['replayID' => self::CURRENT, 'extract_v' => config('analytics.latest_extract_v'), 'loaded_at' => '2026-10-12 00:00:00'],
            ['replayID' => self::OUTDATED, 'extract_v' => config('analytics.extract_v'), 'loaded_at' => '2026-10-12 00:00:00'],
        ]);
        $db->table('replay_archive')->insert([
            'replayID' => self::ARCHIVED, 'bundle' => 'bundles/2026-10-11.bin', 'byte_offset' => 4096, 'byte_length' => 2048,
            'archived_at' => '2026-10-12 00:00:00',
        ]);
        $db->table('replay_fingerprints')->insert(['replayID' => self::UPLOADED, 'fingerprint' => 'f00dfacef00dfacef00dfacef00dface']);
    }

    private function user(array $attributes = []): BattlenetAccount
    {
        $user = BattlenetAccount::forceCreate($attributes + [
            'battlenet_accounts_id' => 900010, 'battlenet_id' => 900010, 'battletag' => 'Tester#1234',
            'blizz_id' => self::BLIZZ_ID, 'region' => self::REGION,
        ]);
        $this->actingAs($user);

        return $user;
    }

    private function onTrial(): BattlenetAccount
    {
        $user = $this->user();
        $this->postJson('/Analytics/Trial')->assertOk()->assertJson(['started' => true]);

        return $user->refresh();
    }

    public function test_without_access_the_data_is_hidden_until_a_trial_starts(): void
    {
        $user = $this->user();
        $access = app(AnalyticsService::class)->access($user);
        $this->assertFalse($access['allowed']);
        $this->assertTrue($access['trial_available']);

        $this->getJson('/Analytics/Status')->assertNotFound();
        $this->postJson('/Analytics/Data/deaths')->assertNotFound();

        $this->postJson('/Analytics/Trial')->assertOk()->assertJson(['started' => true]);
        $this->postJson('/Analytics/Trial')->assertOk()->assertJson(['started' => false]);

        $access = app(AnalyticsService::class)->access($user->refresh());
        $this->assertSame('trial', $access['via']);
        $this->assertSame('2026-11-17 12:00:00', $access['trial_ends_at']->format('Y-m-d H:i:s'));
        $this->getJson('/Analytics/Status')->assertOk();

        // A week later the trial is over and can't be restarted this stage
        $this->travel(8)->days();
        $access = app(AnalyticsService::class)->access($user->refresh());
        $this->assertFalse($access['allowed']);
        $this->assertFalse($access['trial_available']);
        $this->getJson('/Analytics/Status')->assertNotFound();
    }

    public function test_access_through_the_allowlist_patreon_or_a_paid_api_plan(): void
    {
        $user = $this->user();
        $analytics = app(AnalyticsService::class);

        config(['analytics.allowlist' => [self::BLIZZ_ID]]);
        $this->assertSame('allowlist', $analytics->access($user)['via']);
        $this->assertNull($analytics->freeGames($user), 'no free-game cap for the allowlist');
        config(['analytics.allowlist' => [67280]]);

        DB::connection('heroesprofile')->table('patreon_accounts')->insert([
            'battlenet_accounts_id' => $user->battlenet_accounts_id, 'site_flair' => 1, 'ad_free' => 0,
        ]);
        $this->assertSame('patreon', $analytics->access($user->refresh())['via']);
        DB::connection('heroesprofile')->table('patreon_accounts')->where('battlenet_accounts_id', $user->battlenet_accounts_id)->delete();

        // The seeded "basic" API account has a paid plan
        ApiAccount::where('email', 'basic@heroesprofile.test')->update(['analytics_blizz_id' => self::BLIZZ_ID, 'analytics_region' => self::REGION]);
        $this->assertSame('api', $analytics->access($user->refresh())['via']);
    }

    public function test_status_counts_season_games_by_extract_version(): void
    {
        $this->onTrial();

        $this->getJson('/Analytics/Status')->assertOk()->assertJson([
            'total' => 4,
            'done' => 2,
            'outdated' => 1,
            'processing' => false,
            'whats_new' => [config('analytics.extract_versions')[config('analytics.latest_extract_v')]],
            'free' => ['used' => 0, 'limit' => config('analytics.free_games'), 'left' => config('analytics.free_games')],
        ]);
    }

    public function test_a_request_fans_out_to_the_parser_and_finishes_when_the_games_load(): void
    {
        $user = $this->onTrial();

        $this->postJson('/Analytics/Request')->assertOk()->assertExactJson(['queued' => 2, 'updated' => 1, 'already_requested' => false]);
        $this->postJson('/Analytics/Request')->assertOk()->assertJson(['already_requested' => true], 'a second click inside the lock window does nothing');

        $this->assertCount(1, $this->cloudTasks->queued);
        $this->assertSame('https://worker.test/api/v1/internal/analytics/fanout', $this->cloudTasks->queued[0]['url']);
        $this->assertSame([self::UPLOADED, self::ARCHIVED, self::OUTDATED], $this->cloudTasks->queued[0]['body']['replay_ids']);
        $this->assertSame(2, $user->refresh()->analytics_free_games, 'only new games count against the free allowance');
        $this->getJson('/Analytics/Status')->assertJson(['processing' => true]);

        // Cloud Tasks delivers the fan-out to our worker, which queues one parse per replay file it can find
        [$fanOut] = $this->runQueuedTasks();
        $fanOut->assertOk();
        $this->assertSame([
            ['parseType' => 'deep', 'replayID' => self::UPLOADED, 'bucket' => config('analytics.replay_bucket'), 'input' => 'f00dfacef00dfacef00dfacef00dface.StormReplay'],
            ['parseType' => 'deep', 'replayID' => self::ARCHIVED, 'bucket' => config('analytics.archive_bucket'), 'input' => 'bundles/2026-10-11.bin', 'offset' => 4096, 'length' => 2048],
        ], array_column($this->externalTasks, 'body'), 'the outdated game has no replay file to re-parse');
        $this->assertSame(['https://deep-analytics.test/parse'], array_unique(array_column($this->externalTasks, 'audience')));

        // The parser loads them into BigQuery and records each in replay_deep
        DB::connection('heroesprofile')->table('replay_deep')->insert([
            ['replayID' => self::ARCHIVED, 'extract_v' => config('analytics.latest_extract_v'), 'loaded_at' => now()],
            ['replayID' => self::UPLOADED, 'extract_v' => config('analytics.latest_extract_v'), 'loaded_at' => now()],
        ]);
        DB::connection('heroesprofile')->table('replay_deep')->where('replayID', self::OUTDATED)->update(['extract_v' => config('analytics.latest_extract_v')]);

        $this->getJson('/Analytics/Status')->assertJson(['total' => 4, 'done' => 4, 'outdated' => 0, 'processing' => false]);
    }

    public function test_out_of_free_games_only_updates_older_games(): void
    {
        $user = $this->onTrial();
        $user->forceFill(['analytics_free_games' => config('analytics.free_games'), 'analytics_free_stage' => config('analytics.trial_stage')])->save();

        $this->postJson('/Analytics/Request')->assertOk()->assertJson(['queued' => 0, 'updated' => 1]);
        $this->assertSame([self::OUTDATED], $this->cloudTasks->queued[0]['body']['replay_ids']);
    }

    public static function tabs(): array
    {
        return collect(AnalyticsQueryService::TABS)->mapWithKeys(fn ($tab) => [$tab => [$tab]])->all();
    }

    #[DataProvider('tabs')]
    public function test_each_tab_queries_bigquery_with_the_filters(string $tab): void
    {
        $this->onTrial();
        // Real rows: the app's own queries for the sample player, run against BigQuery on 2026-11-18
        // (up to 50 per query). Post-processing runs on them as it does in production.
        $this->bigQuery->rows = json_decode(file_get_contents(__DIR__."/../../Fixtures/analytics-bigquery/{$tab}.json"), true);

        $response = $this->postJson("/Analytics/Data/{$tab}", ['game_type' => ['sl'], 'game_map' => ['Battlefield of Eternity'], 'hero' => 80]);
        $response->assertOk();

        $this->assertMatchesSnapshot("analytics tab {$tab}", [
            'response' => $response->json(),
            'bigquery' => array_map(fn ($call) => [
                'queries' => array_map('sha1', $call['queries']),
                'params' => $call['params'],
            ], $this->bigQuery->calls),
        ]);

        // A second look comes from cache
        $calls = count($this->bigQuery->calls);
        $this->postJson("/Analytics/Data/{$tab}", ['game_type' => ['sl'], 'game_map' => ['Battlefield of Eternity'], 'hero' => 80])->assertOk();
        $this->assertCount($calls, $this->bigQuery->calls);
    }

    public function test_tab_results_are_recomputed_once_more_games_load(): void
    {
        $this->onTrial();
        $this->postJson('/Analytics/Data/stacks')->assertOk();
        $before = count($this->bigQuery->calls);

        DB::connection('heroesprofile')->table('replay_deep')->insert(['replayID' => self::ARCHIVED, 'extract_v' => config('analytics.latest_extract_v'), 'loaded_at' => now()]);

        $this->postJson('/Analytics/Data/stacks')->assertOk();
        $this->assertGreaterThan($before, count($this->bigQuery->calls));
    }

    public function test_deaths_names_the_players_behind_the_numbers(): void
    {
        $this->onTrial();
        $this->bigQuery->rows = ['nemesis_players' => [['blizz_id' => 67280, 'deaths' => 3], ['blizz_id' => 1, 'deaths' => 1]]];

        $response = $this->postJson('/Analytics/Data/deaths')->assertOk();

        $this->assertSame('Zemill', $response->json('nemesis_players.0.battletag'));
        $this->assertNull($response->json('nemesis_players.1.battletag'), 'an unknown player stays unnamed');
        $this->assertArrayHasKey('layout', $response->json());
    }

    public function test_activity_names_abilities_from_their_build_links(): void
    {
        $this->onTrial();
        DB::connection('heroesprofile')->table('ability_links')->insert([
            ['build' => 90000, 'abil_link' => 17, 'ability_id' => 'DeckardHealingPotion', 'hero_id' => 80, 'slot' => 'Q', 'name' => 'Healing Potion', 'icon' => 'potion.png', 'confidence' => 'high'],
            ['build' => 90000, 'abil_link' => 18, 'ability_id' => 'Hearthstone', 'hero_id' => null, 'slot' => 'B', 'name' => 'Hearthstone', 'icon' => 'hearth.png', 'confidence' => 'high'],
        ]);
        $this->bigQuery->rows = [
            'hero_games' => [['who' => 'you', 'hero_id' => 80, 'games' => 4]],
            'ability_usage' => [
                // A newer build than any mapped one uses the newest mapping before it
                ['who' => 'you', 'hero_id' => 80, 'build' => 94786, 'abil_link' => 17, 'games_used' => 3, 'uses' => 30, 'casts' => 28, 'casts_games' => 3, 'avg_first_use' => 40.0],
                ['who' => 'you', 'hero_id' => 80, 'build' => 94786, 'abil_link' => 999, 'games_used' => 1, 'uses' => 1, 'casts' => 1, 'casts_games' => 1, 'avg_first_use' => 5.0],
            ],
        ];

        $usage = $this->postJson('/Analytics/Data/activity')->assertOk()->json('ability_usage');

        $this->assertSame([[
            'who' => 'you', 'hero_id' => 80, 'ability_id' => 'DeckardHealingPotion', 'name' => 'Healing Potion', 'slot' => 'Q',
            'icon' => 'potion.png', 'confidence' => 'high', 'generic' => false, 'games' => 4, 'games_used' => 3, 'uses' => 30,
            'casts' => 28, 'casts_games' => 3, 'avg_first_use' => 40,
        ]], $usage, 'unmapped links are dropped');
    }

    public function test_bad_filters_and_unknown_tabs_are_refused(): void
    {
        $this->onTrial();

        $this->postJson('/Analytics/Data/deaths', ['game_type' => ['zz']])->assertUnprocessable();
        $this->postJson('/Analytics/Data/deaths', ['hero' => 99999])->assertUnprocessable();
        $this->postJson('/Analytics/Data/nope')->assertNotFound();
        $this->postJson('/Analytics/Data/deaths', ['game' => 12345])->assertNotFound();
        $this->assertSame([], $this->bigQuery->calls);
    }

    public function test_one_game_view_pins_every_filter_to_that_game(): void
    {
        $this->onTrial();

        $response = $this->postJson('/Analytics/Data/stacks', ['game' => self::CURRENT, 'game_type' => ['ar']])->assertOk();

        $this->assertSame(self::CURRENT, $response->json('game.replay_id'));
        $params = end($this->bigQuery->calls)['params'];
        $this->assertSame([5], $params['game_types'], 'the game\'s own mode, not the filter');
        $this->assertSame(self::CURRENT, $params['replay']);
    }

    public function test_games_list_and_ability_timeline(): void
    {
        $this->onTrial();

        $games = $this->postJson('/Analytics/Data/games')->assertOk()->json('games');
        $this->assertSame([self::UPLOADED, self::ARCHIVED, self::OUTDATED, self::CURRENT], array_column($games, 'replayID'));
        $this->assertSame([false, false, true, true], array_column($games, 'processed'), 'every season game, processed or not');

        $this->postJson('/Analytics/Data/ability', ['ability_hero' => 80, 'ability_id' => 'DeckardHealingPotion'])->assertOk()->assertJsonStructure(['timeline']);
        $this->postJson('/Analytics/Data/ability', ['ability_hero' => 80, 'ability_id' => 'bad id;'])->assertUnprocessable();
    }

    public function test_replay_viewer_status_process_and_update(): void
    {
        Storage::fake('gcs-analytics');
        $this->onTrial();

        $this->getJson('/Analytics/Match/Single/'.self::CURRENT.'/Status')->assertOk();
        $this->getJson('/Analytics/Match/Single/999/Status')->assertNotFound();
        // Ready, but its document isn't in the bucket
        $this->getJson('/Analytics/Match/Single/'.self::CURRENT.'/Data')->assertNotFound();

        $this->postJson('/Analytics/Match/Single/'.self::CURRENT.'/Process')->assertOk()->assertJson(['queued' => false], 'already loaded');
        $this->postJson('/Analytics/Match/Single/'.self::ARCHIVED.'/Process')->assertOk()->assertJson(['queued' => true]);
        $this->postJson('/Analytics/Match/Single/'.self::ARCHIVED.'/Process')->assertOk()->assertJson(['queued' => false], 'locked after the first');
        $this->postJson('/Analytics/Match/Single/'.self::OUTDATED.'/Update')->assertOk()->assertJson(['queued' => false], 'no replay file to re-parse');

        $this->assertSame([self::ARCHIVED], array_column(array_column($this->cloudTasks->queued, 'body'), 'replayID'));
    }
}
