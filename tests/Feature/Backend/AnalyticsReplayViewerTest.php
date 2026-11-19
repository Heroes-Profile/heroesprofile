<?php

namespace Tests\Feature\Backend;

use App\Models\BattlenetAccount;
use App\Models\Hero;
use App\Services\AnalyticsQueryService;
use App\Services\AnalyticsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Characterization\AssertsSnapshots;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FakeBigQuery;
use Tests\Support\SimulatesCloudTasks;

/**
 * The replay viewer on a real Deep Analytics document: the public sample game (66135342), which
 * the sample player (Zemill) played as Auriel. The document is read from a faked analytics bucket.
 */
class AnalyticsReplayViewerTest extends FeatureTestCase
{
    use AssertsSnapshots;
    use SimulatesCloudTasks;

    private const REPLAY_ID = 66135342;

    private const ZEMILL = 67280;

    private FakeBigQuery $bigQuery;

    private array $doc;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00', 'UTC'));
        $this->setUpCloudTasks();
        config(['analytics.allowlist' => [self::ZEMILL], 'analytics.service_url' => 'https://deep-analytics.test/parse']);

        $this->bigQuery = new FakeBigQuery(app(AnalyticsService::class));
        $this->app->instance(AnalyticsQueryService::class, $this->bigQuery);

        $raw = file_get_contents(__DIR__.'/../../Fixtures/analytics-replay/'.self::REPLAY_ID.'.json.gz');
        $this->doc = json_decode(gzdecode($raw), true);
        Storage::fake('gcs-analytics')->put('doc/'.self::REPLAY_ID.'.json.gz', $raw);

        $this->seedGame();
    }

    /** The game's replay and player rows, as the site stores them, built from the document's own player list. */
    private function seedGame(): void
    {
        $db = DB::connection('heroesprofile');
        $db->table('replay')->insert([
            'replayID' => self::REPLAY_ID, 'game_type' => 5, 'game_date' => '2026-10-18 21:35:23',
            'game_length' => $this->doc['length_s'], 'game_map' => 1, 'game_version' => '2.55.14.98348',
            'region' => 1, 'date_added' => '2026-11-05 20:30:00',
        ]);

        $heroes = Hero::all();
        foreach ($this->doc['players'] as $player) {
            $hero = $heroes->first(fn ($h) => in_array($player['parser_hero'], [$h->alt_name, $h->build_copy_name], true));
            $db->table('player')->insert([
                'replayID' => self::REPLAY_ID, 'blizz_id' => $player['blizz_id'], 'battletag' => 0, 'hero' => $hero->id,
                'hero_level' => 20, 'team' => $player['team'],
                // Team 1 lost three towns and the game
                'winner' => $player['team'] === 0 ? 1 : 0,
            ]);
        }

        $db->table('replay_deep')->insert(['replayID' => self::REPLAY_ID, 'extract_v' => $this->doc['extract_v'], 'loaded_at' => '2026-11-06 00:00:00']);
    }

    private function signIn(int $blizzId = self::ZEMILL): BattlenetAccount
    {
        $user = BattlenetAccount::forceCreate([
            'battlenet_accounts_id' => 900020, 'battlenet_id' => 900020, 'battletag' => 'Viewer#1',
            'blizz_id' => $blizzId, 'region' => 1,
        ]);
        $this->actingAs($user);

        return $user;
    }

    public function test_the_viewer_serves_the_trimmed_document_with_the_viewers_own_player_marked(): void
    {
        $this->signIn();

        $response = $this->getJson('/Analytics/Match/Single/'.self::REPLAY_ID.'/Data')->assertOk();
        $replay = $response->json();

        $this->assertSame(self::REPLAY_ID, $replay['replay_id']);
        $this->assertSame('Battlefield of Eternity', $replay['game_map']);
        $this->assertNull($replay['win_model'], 'BigQuery has no win model here, so the viewer uses its built-in weights');

        $you = array_values(array_filter($replay['players'], fn ($p) => $p['you']));
        $this->assertCount(1, $you);
        $this->assertSame('Auriel', $you[0]['hero']);
        $this->assertArrayNotHasKey('blizz_id', $replay['players'][0], 'no one\'s blizz ID leaves the server');

        $this->assertMatchesSnapshot('analytics replay viewer '.self::REPLAY_ID, [
            'players' => $replay['players'],
            'sections' => array_map(fn ($v) => is_array($v) ? count($v) : $v, array_diff_key($replay, ['players' => 1])),
            'sha1' => sha1(json_encode($replay)),
        ]);
    }

    public function test_another_viewer_is_not_marked_as_a_player(): void
    {
        $this->signIn(900099);

        $players = $this->getJson('/Analytics/Match/Single/'.self::REPLAY_ID.'/Data')->assertOk()->json('players');

        $this->assertSame([], array_values(array_filter(array_column($players, 'you'))));
    }

    public function test_an_older_version_document_offers_an_update_that_re_parses_from_the_archive(): void
    {
        $this->signIn();
        DB::connection('heroesprofile')->table('replay_archive')->insert([
            'replayID' => self::REPLAY_ID, 'bundle' => 'bundles/2026-11-06.bin', 'byte_offset' => 0, 'byte_length' => 1024,
            'archived_at' => '2026-11-07 00:00:00',
        ]);

        $this->getJson('/Analytics/Match/Single/'.self::REPLAY_ID.'/Status')->assertOk()
            ->assertExactJson(['ready' => true, 'current' => $this->doc['extract_v'] >= config('analytics.latest_extract_v')]);

        $this->postJson('/Analytics/Match/Single/'.self::REPLAY_ID.'/Update')->assertOk()->assertJson(['queued' => true]);
        $this->assertSame(self::REPLAY_ID, $this->cloudTasks->queued[0]['body']['replayID']);
        $this->assertSame('https://deep-analytics.test/parse', $this->cloudTasks->queued[0]['url']);
    }

    public function test_a_document_that_is_missing_or_unreadable_is_a_404(): void
    {
        $this->signIn();

        Storage::disk('gcs-analytics')->put('doc/'.self::REPLAY_ID.'.json.gz', gzencode('{"error":"parse failed"}'));
        $this->getJson('/Analytics/Match/Single/'.self::REPLAY_ID.'/Data')->assertNotFound();
    }

    public function test_performance_rates_every_player_from_real_bigquery_rows(): void
    {
        $this->signIn();
        // Real rows: this game's metrics plus the baselines and weights for its heroes, from BigQuery on 2026-11-18
        $this->bigQuery->rows = json_decode(file_get_contents(__DIR__.'/../../Fixtures/analytics-bigquery/performance.json'), true);

        $response = $this->getJson('/Analytics/Match/Single/'.self::REPLAY_ID.'/Performance');
        $this->assertSame(200, $response->status(), $response->getContent());
        $performance = $response->json();

        // Same shape and players as production's own output for this game (resources/analytics-sample/performance.json).
        // Its numbers came from the 2026-11-11 baselines, so only ours are snapshotted.
        $production = json_decode(file_get_contents(__DIR__.'/../../../resources/analytics-sample/performance.json'), true);
        $this->assertSame(array_keys($production), array_keys($performance));
        $this->assertSame($production['min_games'], $performance['min_games']);
        // Listed best-rated first, and the ratings moved with the baselines, so compare by slot
        $bySlot = function (array $players) {
            $rows = array_map(fn ($p) => [$p['idx'], $p['hero_id'], $p['won']], $players);
            sort($rows);

            return $rows;
        };
        $this->assertSame($bySlot($production['players']), $bySlot($performance['players']));
        $this->assertSame(array_keys($production['players'][0]), array_keys($performance['players'][0]));

        $this->assertMatchesSnapshot('analytics replay performance', [
            'response' => $performance,
            'bigquery' => array_map(fn ($call) => ['queries' => array_map('sha1', $call['queries']), 'params' => $call['params']], $this->bigQuery->calls),
        ]);
    }

    public function test_performance_with_no_metrics_has_nothing_to_rate(): void
    {
        $this->signIn();

        $this->getJson('/Analytics/Match/Single/'.self::REPLAY_ID.'/Performance')->assertNotFound();
    }

    public function test_performance_needs_analytics_access(): void
    {
        config(['analytics.allowlist' => []]);
        $this->signIn();

        $this->getJson('/Analytics/Match/Single/'.self::REPLAY_ID.'/Performance')->assertNotFound();
        $this->getJson('/Analytics/Match/Single/'.self::REPLAY_ID.'/Data')->assertOk();
    }
}
