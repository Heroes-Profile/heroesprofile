<?php

namespace Tests\Feature\Backend;

use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Models\SeasonGameVersion;
use App\Services\GlobalQueryService;
use App\Support\GlobalCacheFreshness;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Characterization\SeedFixtures;
use Tests\Feature\FeatureTestCase;
use Tests\Support\SimulatesCloudTasks;

/**
 * The global stats pipeline as production runs it: cache on, async on, the query handed to
 * Cloud Tasks, the worker route running it, the page polling for the result.
 */
class GlobalAsyncQueryTest extends FeatureTestCase
{
    use SeedFixtures;
    use SimulatesCloudTasks;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setGlobalMode(async: true, bypassCache: false);
        config(['cache.use_locally' => true]);

        // Real time, not the frozen 2024 clock: Laravel's cache writes expiry from Carbon, but
        // DatabaseCacheReader (batch children) and the freshness stamps compare against time().
        // In production the two agree.
        Carbon::setTestNow(Carbon::createFromTimestamp(time()));

        $this->setUpCloudTasks();
    }

    protected function tearDown(): void
    {
        // Back to .env.testing's inline, uncached mode for every other test
        $this->setGlobalMode(async: false, bypassCache: true);

        parent::tearDown();
    }

    /** GlobalDataService reads these from the process environment before config. */
    private function setGlobalMode(bool $async, bool $bypassCache): void
    {
        putenv('GLOBAL_ASYNC_ENABLED='.($async ? 'true' : 'false'));
        putenv('GLOBAL_BYPASS_CACHE='.($bypassCache ? 'true' : 'false'));
    }

    private function heroStatsRequest(array $extra = []): array
    {
        return $extra + [
            'timeframe_type' => 'minor',
            'timeframe' => [self::GAME_VERSION],
            'game_type' => ['sl'],
            'statfilter' => 'win_rate',
            'mirror' => 0,
        ];
    }

    /** What the same request returns when the query runs inline, without a cache. */
    private function inlineResult(array $request): array
    {
        $this->setGlobalMode(async: false, bypassCache: true);
        $data = $this->postJson('/api/v1/global/hero', $request)->assertOk()->json();
        $this->setGlobalMode(async: true, bypassCache: false);

        return $data;
    }

    private function cacheKeyFor(string $jobId): string
    {
        return Cache::store('database')->get(app(GlobalQueryService::class)->jobKey($jobId))['cache_key'];
    }

    public function test_a_cold_query_is_queued_run_by_the_worker_and_served_from_cache(): void
    {
        $expected = $this->inlineResult($this->heroStatsRequest());

        $queued = $this->postJson('/api/v1/global/hero', $this->heroStatsRequest());
        $queued->assertStatus(202)->assertJson(['async' => true, 'status' => 'pending']);
        $jobId = $queued->json('job_id');
        $this->assertCount(1, $this->cloudTasks->queued);
        $this->assertSame(['type' => 'global', 'job_id' => $jobId], $this->cloudTasks->queued[0]);

        $this->getJson("/api/v1/global/status/{$jobId}")->assertStatus(202)->assertJson(['status' => 'pending']);

        [$worker] = $this->runQueuedTasks();
        $worker->assertOk()->assertJson(['ok' => true, 'job_id' => $jobId]);

        $polled = $this->getJson("/api/v1/global/status/{$jobId}")->assertOk();
        $this->assertSame($expected, $polled->json(), 'the worker should cache exactly what the inline query returns');

        $hit = $this->postJson('/api/v1/global/hero', $this->heroStatsRequest())->assertOk();
        $this->assertSame($expected, $hit->json());
        $this->assertSame([], $this->cloudTasks->queued, 'a fresh cache hit queues nothing');
        $this->assertNotNull(Cache::store('database')->get(GlobalCacheFreshness::key($this->cacheKeyFor($jobId))), 'the worker stamps when it computed the result');
    }

    public function test_the_same_question_asked_twice_while_queued_shares_one_job(): void
    {
        $first = $this->postJson('/api/v1/global/hero', $this->heroStatsRequest())->assertStatus(202);
        // Same filters arriving in another order, with an empty filter: the same cache key
        $second = $this->postJson('/api/v1/global/hero', array_reverse($this->heroStatsRequest(['region' => null]), true))->assertStatus(202);

        $this->assertSame($first->json('job_id'), $second->json('job_id'));
        $this->assertCount(1, $this->cloudTasks->queued);
    }

    public function test_a_stale_result_is_served_while_a_refresh_is_queued(): void
    {
        // Only the live (newest) patch goes stale; older ones are cached without a freshness window
        $request = $this->heroStatsRequest(['timeframe' => [self::MATCHUP_TALENTS_GAME_VERSION]]);

        $jobId = $this->postJson('/api/v1/global/hero', $request)->json('job_id');
        $this->runQueuedTasks();
        $cacheKey = $this->cacheKeyFor($jobId);
        $fresh = $this->getJson("/api/v1/global/status/{$jobId}")->json();

        // Computed long ago
        Cache::store('database')->put(GlobalCacheFreshness::key($cacheKey), time() - 86400 * 365, 3600);

        $stale = $this->postJson('/api/v1/global/hero', $request)->assertOk();
        $this->assertSame($fresh, $stale->json(), 'stale data is still served straight away');
        $this->assertCount(1, $this->cloudTasks->queued, 'and a refresh is queued');

        $this->runQueuedTasks();
        $this->assertGreaterThan(time() - 60, Cache::store('database')->get(GlobalCacheFreshness::key($cacheKey)), 'the refresh restamps it');
    }

    public function test_a_failed_enqueue_leaves_nothing_pending(): void
    {
        $this->cloudTasks->failNext = new \RuntimeException('Cloud Tasks unavailable');

        $this->postJson('/api/v1/global/hero', $this->heroStatsRequest())->assertStatus(500);

        // The next request starts over instead of waiting on a job that was never queued
        $this->postJson('/api/v1/global/hero', $this->heroStatsRequest())->assertStatus(202);
        $this->assertCount(1, $this->cloudTasks->queued);
    }

    public function test_a_job_that_throws_is_retried_then_reported_failed(): void
    {
        $jobId = 'test-failing-job';
        Cache::store('database')->put(app(GlobalQueryService::class)->jobKey($jobId), [
            'status' => 'pending',
            'cache_key' => 'test-failing-cache-key',
            'handler_class' => GlobalHeroStatsController::class,
            'handler_method' => 'methodThatDoesNotExist',
            'request' => [],
            'cache_ttl_seconds' => 60,
            'origin' => 'site',
            'billed_to' => null,
            'error' => null,
        ], 3600);

        $process = parse_url(self::TASK_HANDLER_URL, PHP_URL_PATH);

        // Cloud Tasks' first two attempts: still running as far as a poller can tell
        $this->postJson($process, ['job_id' => $jobId], $this->taskHeaders(0));
        $this->getJson("/api/v1/global/status/{$jobId}")->assertStatus(202)->assertJson(['status' => 'processing']);

        $this->postJson($process, ['job_id' => $jobId], $this->taskHeaders(1));
        $this->postJson($process, ['job_id' => $jobId], $this->taskHeaders(2));
        $this->getJson("/api/v1/global/status/{$jobId}")->assertStatus(500)->assertJson(['status' => 'failed']);
    }

    public function test_an_api_group_by_map_batch_fans_out_one_job_per_map(): void
    {
        // group_by_map only takes builds from the last six months: the newest seeded patch, released a month ago
        SeasonGameVersion::where('game_version', self::MATCHUP_TALENTS_GAME_VERSION)->update(['date_added' => now()->subMonth()]);

        $key = str_pad('basic_local_api_key_', 60, '0');
        $query = http_build_query([
            'timeframe_type' => 'minor', 'timeframe' => self::MATCHUP_TALENTS_GAME_VERSION, 'game_type' => 'Storm League',
            'group_by_map' => 1, 'api_token' => $key,
        ]);

        $batch = $this->getJson(route('api.external.heroes.stats', [], false).'?'.$query);
        $this->assertSame(202, $batch->status(), $batch->getContent());
        $jobId = $batch->json('job_id');
        $poll = route('api.external.jobs', ['jobId' => $jobId], false).'?api_token='.$key;
        $total = $this->getJson($poll)->assertStatus(202)->assertJson(['completed' => 0])->json('total');
        $this->assertGreaterThan(1, $total, 'one child per playable map');
        $this->assertCount(min($total, (int) config('global.batch_max_in_flight')), $this->cloudTasks->queued, 'the first wave is capped at the in-flight limit');

        // Each finished child queues the next; a polling client also tops the batch up
        $delivered = 0;
        for ($round = 0; $round < 30; $round++) {
            $delivered += count($this->runQueuedTasks());
            // A client waits between polls; the per-minute limits would otherwise fill up
            $this->travel(61)->seconds();
            $result = $this->getJson($poll);
            if ($result->status() !== 202) {
                break;
            }
        }
        $result->assertOk();
        $this->assertSame($total, $delivered, 'every map ran exactly once');
        $maps = array_keys($result->json('data') ?? $result->json());
        $this->assertCount($total, $maps);
        $this->assertContains(self::MAP, $maps, 'results are keyed by map name');

        // A map already computed is served from the child's cache key, as a single-map request would be
        $single = $this->getJson(route('api.external.heroes.stats', [], false).'?'.http_build_query([
            'timeframe_type' => 'minor', 'timeframe' => self::MATCHUP_TALENTS_GAME_VERSION, 'game_type' => 'Storm League',
            'game_map' => self::MAP, 'api_token' => $key,
        ]));
        $this->assertSame(200, $single->status(), $single->getContent());
        $this->assertSame([], $this->cloudTasks->queued);
    }

    public function test_polling_an_unknown_job(): void
    {
        $this->getJson('/api/v1/global/status/no-such-job')->assertNotFound()->assertJson(['status' => 'not_found']);
    }

    public function test_the_worker_only_runs_tasks_signed_for_its_queue(): void
    {
        $process = parse_url(self::TASK_HANDLER_URL, PHP_URL_PATH);
        $body = ['job_id' => 'anything'];

        $this->postJson($process, $body)->assertForbidden();
        $this->postJson($process, $body, ['X-CloudTasks-TaskName' => 'x'])->assertForbidden();
        $this->postJson($process, $body, $this->taskHeaders(0, $this->taskToken(email: 'someone@else.iam.gserviceaccount.com')))->assertForbidden();
        $this->postJson($process, $body, $this->taskHeaders(0, $this->taskToken(audience: 'https://other.test/')))->assertForbidden();
        $this->postJson($process, $body, $this->taskHeaders(0, 'not-a-jwt'))->assertForbidden();
        $this->postJson($process, [], $this->taskHeaders())->assertStatus(400);
    }
}
