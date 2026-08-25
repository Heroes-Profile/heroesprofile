<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
     * Children of one batch running at once. The queue allows 1000 concurrent
     * dispatches and 500 a second, so nothing upstream throttles a fan-out —
     * this ceiling is ours or there is none.
     */
    private const BATCH_MAX_IN_FLIGHT = 10;

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
        int $cacheTtlSeconds
    ): JsonResponse {
        $cache = Cache::store('database');
        $bypassCache = app(GlobalDataService::class)->shouldBypassGlobalCache();
        $parentIndexKey = $this->cacheIndexKey($parentCacheKey);

        if ($bypassCache) {
            $cache->forget($parentCacheKey);
            $cache->forget($parentIndexKey);
        }

        if (! $bypassCache) {
            $cached = $cache->get($parentCacheKey);
            if ($cached !== null) {
                return response()->json($cached)
                    ->header('X-Global-Cache-Status', 'fresh')
                    ->header('X-Global-Async-Mode', 'cache-hit');
            }

            $existing = $cache->get($parentIndexKey);
            if ($existing && in_array($existing['status'], ['pending', 'processing'], true)) {
                $this->topUp($existing['job_id']);

                return $this->batchAccepted($existing['job_id']);
            }
        }

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
            'cache_ttl_seconds' => $cacheTtlSeconds,
            'error' => null,
        ], self::STATUS_TTL_SECONDS);

        $cache->put($parentIndexKey, [
            'job_id' => $jobId,
            'status' => 'pending',
        ], self::STATUS_TTL_SECONDS);

        $this->topUp($jobId);

        return $this->withBypassHeader($this->batchAccepted($jobId), $bypassCache);
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

        foreach ($this->childStates($job['children']) as $label => $state) {
            if ($state['status'] === 'running') {
                $inFlight++;
            } elseif ($state['status'] === 'queued') {
                $queued[] = $label;
            }
        }

        $slots = self::BATCH_MAX_IN_FLIGHT - $inFlight;

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

        try {
            $handler = app($job['handler_class']);

            if (! method_exists($handler, $job['handler_method'])) {
                throw new \RuntimeException("Handler method {$job['handler_method']} not found.");
            }

            $request = new Request($job['request']);
            $data = $handler->{$job['handler_method']}($request);

            $this->markComplete($jobId, $job, $data);

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
     * Where every child of a batch stands, in two cache reads rather than two per
     * child. Worth the indirection: this runs on every poll and again each time a
     * child finishes, so at ninety children the naive form is tens of thousands of
     * round trips over one batch.
     *
     * @param  array<string, array{cache_key: string, request: array<string, mixed>, job_id: ?string}>  $children
     * @return array<string, array{status: string, data?: mixed, error?: string}>
     */
    private function childStates(array $children): array
    {
        $cache = Cache::store('database');

        $resultKeys = [];
        $jobKeys = [];

        foreach ($children as $child) {
            $resultKeys[$child['cache_key']] = true;

            if (($child['job_id'] ?? null) !== null) {
                $jobKeys[$this->jobKey($child['job_id'])] = true;
            }
        }

        $results = $resultKeys === [] ? [] : $cache->many(array_keys($resultKeys));
        $jobs = $jobKeys === [] ? [] : $cache->many(array_keys($jobKeys));

        $states = [];

        foreach ($children as $label => $child) {
            $states[$label] = $this->childState($child, $results, $jobs);
        }

        return $states;
    }

    /**
     * Where one child stands, from its cached result and its own job record.
     *
     * `queued` covers both never-started and started-but-lost — a job record that
     * has aged out, or one that completed and whose result has since expired.
     * Either way the answer is the same: run it.
     *
     * @param  array{cache_key: string, request: array<string, mixed>, job_id: ?string}  $child
     * @param  array<string, mixed>  $results
     * @param  array<string, mixed>  $jobs
     * @return array{status: string, data?: mixed, error?: string}
     */
    private function childState(array $child, array $results, array $jobs): array
    {
        $result = $results[$child['cache_key']] ?? null;

        if ($result !== null) {
            return ['status' => 'complete', 'data' => $result];
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

        $results = [];
        $resolved = 0;

        foreach ($this->childStates($job['children']) as $label => $state) {
            if ($state['status'] === 'complete') {
                $results[$label] = $state['data'];
                $resolved++;
            } elseif ($state['status'] === 'failed') {
                $results[$label] = ['error' => $state['error']];
                $resolved++;
            }
        }

        $total = count($job['children']);

        if ($resolved < $total) {
            return $this->batchAccepted($jobId, $resolved, $total);
        }

        $ttl = max(60, (int) ($job['cache_ttl_seconds'] ?? 3600));
        $cache->put($job['cache_key'], $results, $ttl);

        $job['status'] = 'complete';
        $job['error'] = null;
        $cache->put($this->jobKey($jobId), $job, self::STATUS_TTL_SECONDS);
        $cache->put($this->cacheIndexKey($job['cache_key']), [
            'job_id' => $jobId,
            'status' => 'complete',
        ], self::STATUS_TTL_SECONDS);

        return response()->json($results)
            ->header('X-Global-Cache-Status', 'fresh');
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
