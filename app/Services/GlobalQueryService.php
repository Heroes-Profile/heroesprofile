<?php

namespace App\Services;

use App\Http\Middleware\TrackSlowRequests;
use App\Support\DatabaseCacheReader;
use App\Support\GlobalCacheFreshness;
use App\Support\GlobalCacheWindow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Facades\Flare;

class GlobalQueryService
{
    private const STATUS_TTL_SECONDS = 7200;

    /**
     * What the `global-queries` queue is configured to retry. A child that failed
     * is not finished until the queue has spent these, so a batch must not report
     * an error while an attempt is still coming.
     */
    private const MAX_ATTEMPTS = 3;

    /**
     * Request attribute set only by the public API, after its own support check and
     * batch rate limit. Without it `group_by_map` is ignored, so the site's anonymous
     * routes can't be made to fan one request out into a query per map.
     */
    public const GROUP_BY_MAP_ALLOWED = 'global.group_by_map_allowed';

    /** How long a batch result holding a failed child is kept, so it is retried soon. */
    private const PARTIAL_RESULT_TTL_SECONDS = 300;

    /** Children of one batch running at once. See `config/global.php`. */
    private function batchMaxInFlight(): int
    {
        return max(1, (int) config('global.batch_max_in_flight', 10));
    }

    public function handle(
        string $cacheKey,
        string $handlerClass,
        string $handlerMethod,
        array $requestData,
        int $cacheTtlSeconds
    ): JsonResponse {
        $cache = Cache::store('database');
        $bypassCache = app(GlobalDataService::class)->shouldBypassGlobalCache();
        $cacheIndexKey = $this->cacheIndexKey($cacheKey);

        if ($bypassCache) {
            $cache->forget($cacheKey);
            $cache->forget($cacheIndexKey);
        }

        if (! $bypassCache && $cacheTtlSeconds > 0) {
            $cached = $cache->get($cacheKey);
            if ($cached !== null) {
                return response()->json($cached)
                    ->header('X-Global-Cache-Status', 'fresh')
                    ->header('X-Global-Async-Mode', 'cache-hit');
            }
        }

        $existing = $cache->get($cacheIndexKey);

        if ($existing && in_array($existing['status'], ['pending', 'processing'], true)) {
            return $this->acceptedResponse($existing['job_id'], $existing['status']);
        }

        $jobId = (string) Str::uuid();

        $jobPayload = [
            'status' => 'pending',
            'cache_key' => $cacheKey,
            'handler_class' => $handlerClass,
            'handler_method' => $handlerMethod,
            'request' => $requestData,
            'cache_ttl_seconds' => $cacheTtlSeconds,
            'origin' => $this->origin(),
            'error' => null,
        ];

        $cache->put($this->jobKey($jobId), $jobPayload, self::STATUS_TTL_SECONDS);
        $cache->put($cacheIndexKey, [
            'job_id' => $jobId,
            'status' => 'pending',
        ], self::STATUS_TTL_SECONDS);

        try {
            app(CloudTasksDispatcher::class)->dispatch($jobId);
        } catch (\Throwable $e) {
            $cache->forget($this->jobKey($jobId));
            $cache->forget($cacheIndexKey);
            Log::error('Failed to enqueue Cloud Task after retries', [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $this->withBypassHeader($this->acceptedResponse($jobId, 'pending'), $bypassCache);
    }

    public function dispatchAsync(
        string $cacheKey,
        string $handlerClass,
        string $handlerMethod,
        array $requestData,
        int $cacheTtlSeconds
    ): JsonResponse {
        $cache = Cache::store('database');
        $cacheIndexKey = $this->cacheIndexKey($cacheKey);

        $existing = $cache->get($cacheIndexKey);
        if ($existing && in_array($existing['status'], ['pending', 'processing'], true)) {
            return $this->acceptedResponse($existing['job_id'], $existing['status']);
        }

        $jobId = (string) Str::uuid();

        $jobPayload = [
            'status' => 'pending',
            'cache_key' => $cacheKey,
            'handler_class' => $handlerClass,
            'handler_method' => $handlerMethod,
            'request' => $requestData,
            'cache_ttl_seconds' => $cacheTtlSeconds,
            'origin' => $this->origin(),
            'error' => null,
        ];

        $cache->put($this->jobKey($jobId), $jobPayload, self::STATUS_TTL_SECONDS);
        $cache->put($cacheIndexKey, ['job_id' => $jobId, 'status' => 'pending'], self::STATUS_TTL_SECONDS);

        try {
            app(CloudTasksDispatcher::class)->dispatch($jobId);
        } catch (\Throwable $e) {
            $cache->forget($this->jobKey($jobId));
            $cache->forget($cacheIndexKey);
            Log::error('Failed to enqueue Cloud Task (dispatchAsync)', [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $this->acceptedResponse($jobId, 'pending');
    }

    public function dispatchIfNotPending(
        string $cacheKey,
        string $handlerClass,
        string $handlerMethod,
        array $requestData,
        int $cacheTtlSeconds
    ): void {
        $cache = Cache::store('database');
        $cacheIndexKey = $this->cacheIndexKey($cacheKey);

        $existing = $cache->get($cacheIndexKey);
        if ($existing && in_array($existing['status'], ['pending', 'processing'], true)) {
            return;
        }

        $jobId = (string) Str::uuid();

        $jobPayload = [
            'status' => 'pending',
            'cache_key' => $cacheKey,
            'handler_class' => $handlerClass,
            'handler_method' => $handlerMethod,
            'request' => $requestData,
            'cache_ttl_seconds' => $cacheTtlSeconds,
            'origin' => $this->origin(),
            'error' => null,
        ];

        $cache->put($this->jobKey($jobId), $jobPayload, self::STATUS_TTL_SECONDS);
        $cache->put($cacheIndexKey, [
            'job_id' => $jobId,
            'status' => 'pending',
        ], self::STATUS_TTL_SECONDS);

        try {
            app(CloudTasksDispatcher::class)->dispatch($jobId);
        } catch (\Throwable $e) {
            $cache->forget($this->jobKey($jobId));
            $cache->forget($cacheIndexKey);
            Log::error('Failed to enqueue Cloud Task (dispatchIfNotPending)', [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * One job id over many independent queries.
     *
     * Each child is an ordinary job under its own cache key, so anything already
     * computed under that key costs the batch nothing. The parent runs no query of
     * its own: it is the record that lets one caller poll one id, and it completes
     * when every child has resolved.
     *
     * Children are dispatched a few at a time rather than all at once. See
     * `topUp()`.
     *
     * @param  array<string, array{cache_key: string, request: array<string, mixed>}>  $children
     *                                                                                            Keyed by the label each answers under in the assembled result.
     */
    public function dispatchBatch(
        string $parentCacheKey,
        array $children,
        string $handlerClass,
        string $handlerMethod,
        GlobalCacheWindow $window
    ): JsonResponse {
        $cache = Cache::store('database');
        $bypassCache = app(GlobalDataService::class)->shouldBypassGlobalCache();
        $asyncEnabled = app(GlobalDataService::class)->isGlobalAsyncEnabled();
        $parentIndexKey = $this->cacheIndexKey($parentCacheKey);

        if ($bypassCache) {
            $cache->forget($parentCacheKey);
            $cache->forget($parentIndexKey);
        }

        if (! $bypassCache) {
            $cached = $cache->get($parentCacheKey);
            if ($cached !== null) {
                $status = 'fresh';

                if ($asyncEnabled && GlobalCacheFreshness::isStale($parentCacheKey, $window)) {
                    $status = 'stale';
                    $this->refreshBatch($parentCacheKey, $children, $handlerClass, $handlerMethod, $window);
                }

                return response()->json($cached)
                    ->header('X-Global-Cache-Status', $status)
                    ->header('X-Global-Async-Mode', 'cache-hit');
            }

            $existing = $cache->get($parentIndexKey);
            if ($existing && in_array($existing['status'], ['pending', 'processing'], true)) {
                $this->topUp($existing['job_id']);

                return $this->batchAccepted($existing['job_id']);
            }
        }

        // Cloud Tasks is configured on the deployed service only. Without this a
        // batch run anywhere else dispatches nothing and reports 0 of 90 forever.
        // The single-query path makes the same choice — see HandlesAsyncGlobalQueries.
        if (! $asyncEnabled) {
            return $this->runBatchInline($parentCacheKey, $children, $handlerClass, $handlerMethod, $window->ttl, $bypassCache);
        }

        $jobId = $this->startBatch($parentCacheKey, $children, $handlerClass, $handlerMethod, $window->ttl, null);

        return $this->withBypassHeader($this->batchAccepted($jobId), $bypassCache);
    }

    /**
     * Rerun a stale batch's stale children behind the result being served.
     *
     * Nobody polls a refresh, so `topUp()` assembles it once the last child
     * resolves. Never allowed to fail the request serving the stale result.
     *
     * @param  array<string, array{cache_key: string, request: array<string, mixed>}>  $children
     */
    private function refreshBatch(
        string $parentCacheKey,
        array $children,
        string $handlerClass,
        string $handlerMethod,
        GlobalCacheWindow $window
    ): void {
        try {
            $existing = Cache::store('database')->get($this->cacheIndexKey($parentCacheKey));

            if ($existing && in_array($existing['status'], ['pending', 'processing'], true)) {
                $this->topUp($existing['job_id']);

                return;
            }

            $this->startBatch($parentCacheKey, $children, $handlerClass, $handlerMethod, $window->ttl, time() - $window->fresh);
        } catch (\Throwable $exception) {
            Log::warning('Stale batch refresh failed', [
                'cache_key' => $parentCacheKey,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, array{cache_key: string, request: array<string, mixed>}>  $children
     * @param  ?int  $refreshBefore  Children computed before this time are rerun. Null for a new batch.
     */
    private function startBatch(
        string $parentCacheKey,
        array $children,
        string $handlerClass,
        string $handlerMethod,
        int $cacheTtlSeconds,
        ?int $refreshBefore
    ): string {
        $cache = Cache::store('database');
        $jobId = (string) Str::uuid();
        $seeded = [];

        foreach ($children as $label => $child) {
            $seeded[$label] = $child + ['job_id' => null];
        }

        $cache->put($this->jobKey($jobId), [
            'type' => 'batch',
            'status' => 'pending',
            'cache_key' => $parentCacheKey,
            'handler_class' => $handlerClass,
            'handler_method' => $handlerMethod,
            'children' => $seeded,
            'origin' => $this->origin(),
            'cache_ttl_seconds' => $cacheTtlSeconds,
            'refresh_before' => $refreshBefore,
            'error' => null,
        ], self::STATUS_TTL_SECONDS);

        $cache->put($this->cacheIndexKey($parentCacheKey), [
            'job_id' => $jobId,
            'status' => 'pending',
        ], self::STATUS_TTL_SECONDS);

        $this->topUp($jobId);

        return $jobId;
    }

    /**
     * Every child in turn, inside the request itself.
     *
     * Not a serious way to answer a ninety-hero question — it is the only way to
     * exercise one at all where Cloud Tasks is not configured, which is everywhere
     * but the deployed service. Results are written to the same per-child keys the
     * queued path uses, so nothing is wasted.
     *
     * @param  array<string, array{cache_key: string, request: array<string, mixed>}>  $children
     */
    private function runBatchInline(
        string $parentCacheKey,
        array $children,
        string $handlerClass,
        string $handlerMethod,
        int $cacheTtlSeconds,
        bool $bypassCache
    ): JsonResponse {
        ignore_user_abort(true);
        ini_set('max_execution_time', '900');

        $cache = Cache::store('database');
        $handler = app($handlerClass);
        $ttl = max(60, $cacheTtlSeconds);
        $results = [];

        foreach ($children as $label => $child) {
            $cached = $bypassCache ? null : $cache->get($child['cache_key']);

            if ($cached !== null) {
                $results[$label] = $cached;

                continue;
            }

            try {
                $data = $handler->{$handlerMethod}(new Request($child['request']));
                $cache->put($child['cache_key'], $data, $ttl);
                GlobalCacheFreshness::stamp($child['cache_key'], $ttl);
                $results[$label] = $data;
            } catch (\Throwable $exception) {
                Log::error('Inline batch child failed', [
                    'label' => $label,
                    'error' => $exception->getMessage(),
                ]);

                $results[$label] = ['error' => $exception->getMessage()];
            }
        }

        $cache->put($parentCacheKey, $results, $ttl);
        GlobalCacheFreshness::stamp($parentCacheKey, $ttl);

        return response()->json($results)
            ->header('X-Global-Async-Mode', 'sync');
    }

    /**
     * Start as many of a batch's outstanding children as the in-flight ceiling
     * allows.
     *
     * Recomputed from the children's own state every time rather than tracked with
     * a cursor, so calling it twice is harmless and calling it late repairs the
     * batch. That matters on Cloud Run: a worker evicted mid-request never hands
     * off to the next child, and without a second trigger the batch would sit at
     * whatever was in flight forever. A completing child calls this, and so does
     * every poll.
     */
    public function topUp(string $parentJobId): void
    {
        $cache = Cache::store('database');
        $job = $cache->get($this->jobKey($parentJobId));

        if (! is_array($job) || ($job['type'] ?? null) !== 'batch' || $job['status'] === 'complete') {
            return;
        }

        $queued = [];
        $inFlight = 0;
        $states = $this->childStates($job['children'], $job['refresh_before'] ?? null);

        foreach ($states as $label => $state) {
            if ($state['status'] === 'running') {
                $inFlight++;
            } elseif ($state['status'] === 'queued') {
                $queued[] = $label;
            }
        }

        // Every child resolved: assemble here rather than waiting on a poll, which a
        // background refresh never gets.
        if ($queued === [] && $inFlight === 0) {
            $this->assembleBatch($parentJobId, $job, $states);

            return;
        }

        $slots = $this->batchMaxInFlight() - $inFlight;

        if ($slots <= 0 || $queued === []) {
            return;
        }

        foreach (array_slice($queued, 0, $slots) as $label) {
            $childJobId = $this->dispatchChild($parentJobId, $label, $job['children'][$label], $job);

            if ($childJobId !== null) {
                $job['children'][$label]['job_id'] = $childJobId;
            }
        }

        $cache->put($this->jobKey($parentJobId), $job, self::STATUS_TTL_SECONDS);
    }

    public function poll(string $jobId): JsonResponse
    {
        $cache = Cache::store('database');
        $job = $cache->get($this->jobKey($jobId));

        if (! is_array($job)) {
            return response()->json([
                'async' => true,
                'status' => 'not_found',
                'job_id' => $jobId,
            ], 404);
        }

        if (($job['type'] ?? null) === 'batch') {
            return $this->pollBatch($jobId, $job);
        }

        if ($job['status'] === 'complete') {
            $data = $cache->get($job['cache_key']);

            if ($data === null) {
                return response()->json([
                    'async' => true,
                    'status' => 'failed',
                    'job_id' => $jobId,
                    'error' => 'Cached result missing after job completion.',
                ], 500);
            }

            return response()->json($data)
                ->header('X-Global-Cache-Status', 'fresh');
        }

        if ($job['status'] === 'failed') {
            // Cloud Tasks has another attempt coming, so the job is still running.
            if (($job['attempts'] ?? self::MAX_ATTEMPTS) < self::MAX_ATTEMPTS) {
                return $this->acceptedResponse($jobId, 'processing');
            }

            return response()->json([
                'async' => true,
                'status' => 'failed',
                'job_id' => $jobId,
                'error' => $job['error'] ?? 'Query failed.',
            ], 500);
        }

        return $this->acceptedResponse($jobId, $job['status']);
    }

    /**
     * @param  int  $attempt  Which of the queue's attempts this is, counting from 1.
     *                        A child is only terminally failed once these run out.
     */
    public function runJob(string $jobId, int $attempt = 1): void
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', '900');

        $cache = Cache::store('database');
        $job = $cache->get($this->jobKey($jobId));

        if (! is_array($job)) {
            throw new \RuntimeException("Job {$jobId} not found.");
        }

        // Parents carry no query and are never enqueued. One reaching here means a
        // task was created against the wrong id.
        if (($job['type'] ?? null) === 'batch') {
            throw new \RuntimeException("Job {$jobId} is a batch parent and has nothing to run.");
        }

        if ($job['status'] === 'complete') {
            return;
        }

        $this->markProcessing($jobId, $job);

        // The worker's own request body is only the job id. Set before running so a
        // crash, out-of-memory included, is reported with what was actually asked.
        $input = Arr::except($job['request'] ?? [], TrackSlowRequests::HIDDEN_INPUT);
        Flare::context('job_handler', $job['handler_class'].'@'.$job['handler_method']);
        Flare::context('job_origin', $job['origin'] ?? 'unknown');
        Flare::context('job_input', $input);
        Flare::context('job_attempt', $attempt);

        $start = microtime(true);

        try {
            $handler = app($job['handler_class']);

            if (! method_exists($handler, $job['handler_method'])) {
                throw new \RuntimeException("Handler method {$job['handler_method']} not found.");
            }

            $request = new Request($job['request']);
            $data = $handler->{$job['handler_method']}($request);

            $this->markComplete($jobId, $job, $data);

            $seconds = round(microtime(true) - $start, 2);

            if ($seconds >= TrackSlowRequests::THRESHOLD_SECONDS) {
                Flare::context('duration_seconds', $seconds);
                Flare::reportMessage(
                    "Slow job ({$seconds}s): ".class_basename($job['handler_class']).'@'.$job['handler_method'],
                    'error'
                );
            }

            Log::info('Global query job complete', [
                'job_id' => $jobId,
                'cache_key' => $job['cache_key'],
            ]);
        } catch (\Throwable $exception) {
            Log::error('Global query job failed', [
                'job_id' => $jobId,
                'cache_key' => $job['cache_key'],
                'error' => $exception->getMessage(),
            ]);

            $this->markFailed($jobId, $job, $exception->getMessage(), $attempt);
            $this->topUpParent($job);

            throw $exception;
        }

        $this->topUpParent($job);
    }

    /**
     * Who created the job. Keys are shared, so anyone asking the same question
     * afterwards waits on this job rather than starting their own.
     */
    private function origin(): string
    {
        $request = request();

        if ($request->hasHeader('X-CloudTasks-TaskName')) {
            return 'worker';
        }

        return str_starts_with($request->route()?->getName() ?? '', 'api.external.') ? 'api' : 'site';
    }

    public function jobKey(string $jobId): string
    {
        return 'global_job:'.$jobId;
    }

    public function cacheIndexKey(string $cacheKey): string
    {
        return 'global_job_by_cache:'.hash('sha256', $cacheKey);
    }

    private function markProcessing(string $jobId, array $job): void
    {
        $cache = Cache::store('database');

        $job['status'] = 'processing';
        $cache->put($this->jobKey($jobId), $job, self::STATUS_TTL_SECONDS);
        $cache->put($this->cacheIndexKey($job['cache_key']), [
            'job_id' => $jobId,
            'status' => 'processing',
        ], self::STATUS_TTL_SECONDS);
    }

    private function markComplete(string $jobId, array $job, mixed $data): void
    {
        $cache = Cache::store('database');
        $ttl = max(60, (int) ($job['cache_ttl_seconds'] ?? 3600));

        $cache->put($job['cache_key'], $data, $ttl);
        GlobalCacheFreshness::stamp($job['cache_key'], $ttl);

        $job['status'] = 'complete';
        $job['error'] = null;
        $cache->put($this->jobKey($jobId), $job, self::STATUS_TTL_SECONDS);
        $cache->put($this->cacheIndexKey($job['cache_key']), [
            'job_id' => $jobId,
            'status' => 'complete',
        ], self::STATUS_TTL_SECONDS);
    }

    private function markFailed(string $jobId, array $job, string $error, int $attempt = 1): void
    {
        $cache = Cache::store('database');

        $job['status'] = 'failed';
        $job['error'] = $error;
        $job['attempts'] = $attempt;
        $cache->put($this->jobKey($jobId), $job, self::STATUS_TTL_SECONDS);

        // Cloud Tasks retries up to MAX_ATTEMPTS. Until the last one the job is still in
        // flight, so its index stays and a fresh request waits for it instead of starting
        // a duplicate.
        if ($attempt < self::MAX_ATTEMPTS) {
            $cache->put($this->cacheIndexKey($job['cache_key']), [
                'job_id' => $jobId,
                'status' => 'processing',
            ], self::STATUS_TTL_SECONDS);

            return;
        }

        $cache->forget($this->cacheIndexKey($job['cache_key']));
    }

    /**
     * Hand off to the next child in the batch this job belongs to, if any.
     *
     * Never allowed to fail the job: a child that has already done its work should
     * not be retried because the hand-off did not land, and a poll will top the
     * batch up regardless.
     */
    private function topUpParent(array $job): void
    {
        $parentJobId = $job['parent_job_id'] ?? null;

        if ($parentJobId === null) {
            return;
        }

        try {
            $this->topUp($parentJobId);
        } catch (\Throwable $exception) {
            Log::warning('Batch top-up from child job failed', [
                'parent_job_id' => $parentJobId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Where every child of a batch stands, without reading any of their results.
     *
     * This runs on every poll and again each time a child finishes, so it is kept
     * deliberately thin: one query asking which result keys exist, and a second for
     * the job records of only those children that have no result yet. The results
     * themselves are whole query outputs and are read once, at assembly, by
     * `batchResults()`.
     *
     * A refresh adds one query for the stamps of the children that have a result: a
     * result computed before `$refreshBefore` is not complete, it is due a rerun.
     *
     * @param  array<string, array{cache_key: string, request: array<string, mixed>, job_id: ?string}>  $children
     * @return array<string, array{status: string, error?: string}>
     */
    private function childStates(array $children, ?int $refreshBefore = null): array
    {
        $resultKeys = [];

        foreach ($children as $child) {
            $resultKeys[$child['cache_key']] = true;
        }

        $complete = DatabaseCacheReader::existing(array_keys($resultKeys));

        if ($refreshBefore !== null) {
            $stampKeys = [];

            foreach ($complete as $key => $exists) {
                if ($exists) {
                    $stampKeys[GlobalCacheFreshness::key($key)] = true;
                }
            }

            $stamps = DatabaseCacheReader::many(array_keys($stampKeys));

            foreach ($complete as $key => $exists) {
                $complete[$key] = $exists && (int) ($stamps[GlobalCacheFreshness::key($key)] ?? 0) >= $refreshBefore;
            }
        }

        $jobKeys = [];

        foreach ($children as $child) {
            if (! ($complete[$child['cache_key']] ?? false) && ($child['job_id'] ?? null) !== null) {
                $jobKeys[$this->jobKey($child['job_id'])] = true;
            }
        }

        $jobs = DatabaseCacheReader::many(array_keys($jobKeys));

        $states = [];

        foreach ($children as $label => $child) {
            $states[$label] = $this->childState($child, $complete, $jobs);
        }

        return $states;
    }

    /**
     * A finished batch's payload, reading each child's result once.
     *
     * A refresh that failed keeps the result it was refreshing rather than
     * swapping good data for an error.
     *
     * @param  array<string, array{cache_key: string, request: array<string, mixed>, job_id: ?string}>  $children
     * @param  array<string, array{status: string, error?: string}>  $states
     * @return array{0: array<string, mixed>, 1: bool} The payload, and whether any label is an error.
     */
    private function batchResults(array $children, array $states): array
    {
        $keys = [];

        foreach ($children as $child) {
            $keys[$child['cache_key']] = true;
        }

        $results = DatabaseCacheReader::many(array_keys($keys));
        $payload = [];
        $hasErrors = false;

        foreach ($children as $label => $child) {
            $result = $results[$child['cache_key']] ?? null;

            if ($states[$label]['status'] === 'complete' || $result !== null) {
                $payload[$label] = $result;

                continue;
            }

            $payload[$label] = ['error' => $states[$label]['error'] ?? 'Query failed.'];
            $hasErrors = true;
        }

        return [$payload, $hasErrors];
    }

    /**
     * Where one child stands, from whether its result exists and what its own job
     * record says.
     *
     * `queued` covers both never-started and started-but-lost — a job record that
     * has aged out, or one that completed and whose result has since expired.
     * Either way the answer is the same: run it.
     *
     * @param  array{cache_key: string, request: array<string, mixed>, job_id: ?string}  $child
     * @param  array<string, bool>  $complete
     * @param  array<string, mixed>  $jobs
     * @return array{status: string, error?: string}
     */
    private function childState(array $child, array $complete, array $jobs): array
    {
        if ($complete[$child['cache_key']] ?? false) {
            return ['status' => 'complete'];
        }

        $childJobId = $child['job_id'] ?? null;
        $childJob = $childJobId === null ? null : ($jobs[$this->jobKey($childJobId)] ?? null);

        if (! is_array($childJob) || $childJob['status'] === 'complete') {
            return ['status' => 'queued'];
        }

        if ($childJob['status'] === 'failed') {
            return ($childJob['attempts'] ?? 0) >= self::MAX_ATTEMPTS
                ? ['status' => 'failed', 'error' => $childJob['error'] ?? 'Query failed.']
                : ['status' => 'running'];
        }

        return ['status' => 'running'];
    }

    /**
     * Enqueue one child, unless its cache key already has a job in flight — which
     * is what keeps two simultaneous top-ups from starting the same query twice.
     *
     * @param  array{cache_key: string, request: array<string, mixed>, job_id: ?string}  $child
     * @param  array<string, mixed>  $parent
     */
    private function dispatchChild(string $parentJobId, string $label, array $child, array $parent): ?string
    {
        $cache = Cache::store('database');
        $cacheIndexKey = $this->cacheIndexKey($child['cache_key']);

        $existing = $cache->get($cacheIndexKey);
        if ($existing && in_array($existing['status'], ['pending', 'processing'], true)) {
            return $existing['job_id'];
        }

        $jobId = (string) Str::uuid();

        $cache->put($this->jobKey($jobId), [
            'status' => 'pending',
            'cache_key' => $child['cache_key'],
            'handler_class' => $parent['handler_class'],
            'handler_method' => $parent['handler_method'],
            'request' => $child['request'],
            'cache_ttl_seconds' => $parent['cache_ttl_seconds'],
            'origin' => $parent['origin'] ?? null,
            'parent_job_id' => $parentJobId,
            'label' => $label,
            'attempts' => 0,
            'error' => null,
        ], self::STATUS_TTL_SECONDS);

        $cache->put($cacheIndexKey, [
            'job_id' => $jobId,
            'status' => 'pending',
        ], self::STATUS_TTL_SECONDS);

        try {
            app(CloudTasksDispatcher::class)->dispatch($jobId);
        } catch (\Throwable $exception) {
            $cache->forget($this->jobKey($jobId));
            $cache->forget($cacheIndexKey);
            Log::error('Failed to enqueue Cloud Task (batch child)', [
                'parent_job_id' => $parentJobId,
                'label' => $label,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        return $jobId;
    }

    /**
     * A batch's result, or how far along it is.
     *
     * A child that has exhausted the queue's attempts answers under its own label
     * with an `error` rather than sinking the whole batch, so one bad hero costs
     * that hero and nothing else.
     *
     * @param  array<string, mixed>  $job
     */
    private function pollBatch(string $jobId, array $job): JsonResponse
    {
        $cache = Cache::store('database');

        if ($job['status'] === 'complete') {
            $data = $cache->get($job['cache_key']);

            if ($data !== null) {
                return response()->json($data)
                    ->header('X-Global-Cache-Status', 'fresh');
            }

            // Assembled once, but the result has since aged out. Reopen the batch,
            // or `topUp()` would decline to restart anything and this would answer
            // 202 forever.
            $job['status'] = 'pending';
            $cache->put($this->jobKey($jobId), $job, self::STATUS_TTL_SECONDS);
        }

        $this->topUp($jobId);

        $job = $cache->get($this->jobKey($jobId));

        if (! is_array($job)) {
            return response()->json([
                'async' => true,
                'status' => 'not_found',
                'job_id' => $jobId,
            ], 404);
        }

        // `topUp()` assembles the batch once every child has resolved.
        if ($job['status'] === 'complete') {
            $data = $cache->get($job['cache_key']);

            if ($data !== null) {
                return response()->json($data)
                    ->header('X-Global-Cache-Status', 'fresh');
            }
        }

        $states = $this->childStates($job['children'], $job['refresh_before'] ?? null);
        $resolved = count(array_filter(
            $states,
            static fn ($state) => in_array($state['status'], ['complete', 'failed'], true)
        ));

        return $this->batchAccepted($jobId, $resolved, count($job['children']));
    }

    /**
     * @param  array<string, mixed>  $job
     * @param  array<string, array{status: string, error?: string}>  $states
     */
    private function assembleBatch(string $jobId, array $job, array $states): void
    {
        $cache = Cache::store('database');

        [$results, $hasErrors] = $this->batchResults($job['children'], $states);

        // A failed child is usually transient; keeping its error for the data TTL would
        // show it to everyone for weeks. Briefly, so a later request retries just that one.
        $ttl = $hasErrors
            ? self::PARTIAL_RESULT_TTL_SECONDS
            : max(60, (int) ($job['cache_ttl_seconds'] ?? 3600));
        $cache->put($job['cache_key'], $results, $ttl);
        GlobalCacheFreshness::stamp($job['cache_key'], $ttl);

        $job['status'] = 'complete';
        $job['error'] = null;
        $cache->put($this->jobKey($jobId), $job, self::STATUS_TTL_SECONDS);
        $cache->put($this->cacheIndexKey($job['cache_key']), [
            'job_id' => $jobId,
            'status' => 'complete',
        ], self::STATUS_TTL_SECONDS);
    }

    private function batchAccepted(string $jobId, ?int $completed = null, ?int $total = null): JsonResponse
    {
        $payload = [
            'async' => true,
            'status' => 'pending',
            'job_id' => $jobId,
        ];

        if ($total !== null) {
            $payload['completed'] = $completed;
            $payload['total'] = $total;
        }

        return response()->json($payload, 202)
            ->header('X-Global-Async-Mode', 'accepted')
            ->header('X-Global-Job-Id', $jobId);
    }

    private function acceptedResponse(string $jobId, string $status): JsonResponse
    {
        return response()->json([
            'async' => true,
            'status' => $status,
            'job_id' => $jobId,
        ], 202)
            ->header('X-Global-Async-Mode', 'accepted')
            ->header('X-Global-Job-Id', $jobId);
    }

    private function withBypassHeader(JsonResponse $response, bool $bypassCache): JsonResponse
    {
        if ($bypassCache) {
            $response->header('X-Global-Cache-Bypass', 'true');
        }

        return $response;
    }
}
