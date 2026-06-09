<?php

namespace App\Services;

use App\Jobs\ProcessGlobalAsyncQueryJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GlobalAsyncQueryService
{
    private const STATUS_TTL_SECONDS = 7200;

    public function handle(
        string $cacheKey,
        string $handlerClass,
        string $handlerMethod,
        array $requestData,
        int $cacheFreshSeconds,
        ?int $cacheStaleSeconds = null
    ): JsonResponse {
        if ($cacheFreshSeconds <= 0) {
            $handler = app($handlerClass);
            $data = $handler->{$handlerMethod}(new Request($requestData));

            return response()->json($data);
        }

        $cacheStaleSeconds = $cacheStaleSeconds ?? max($cacheFreshSeconds * 2, $cacheFreshSeconds + 3600);
        $cache = Cache::store('database');
        $entry = $this->normalizeCacheEntry($cache->get($cacheKey));

        if ($entry !== null) {
            $age = $this->getCacheAge($entry);

            if ($age < $entry['fresh_ttl']) {
                return $this->jsonCachedResponse($entry['data'], 'fresh');
            }

            if ($age < $entry['stale_ttl']) {
                $this->dispatchBackgroundJob(
                    $cacheKey,
                    $handlerClass,
                    $handlerMethod,
                    $requestData,
                    $cacheFreshSeconds,
                    $cacheStaleSeconds
                );

                return $this->jsonCachedResponse($entry['data'], 'stale');
            }
        }

        $statusKey = $this->statusKeyForCacheKey($cacheKey);
        $existingStatus = $cache->get($statusKey);

        if ($existingStatus && in_array($existingStatus['status'], ['pending', 'processing'], true)) {
            return response()->json([
                'async' => true,
                'status' => $existingStatus['status'],
                'job_id' => $existingStatus['job_id'],
            ], 202);
        }

        $jobId = $this->dispatchBackgroundJob(
            $cacheKey,
            $handlerClass,
            $handlerMethod,
            $requestData,
            $cacheFreshSeconds,
            $cacheStaleSeconds
        );

        return response()->json([
            'async' => true,
            'status' => 'pending',
            'job_id' => $jobId,
        ], 202);
    }

    public function poll(string $jobId): JsonResponse
    {
        $cache = Cache::store('database');
        $job = $cache->get($this->jobKey($jobId));

        if (! $job) {
            return response()->json([
                'async' => true,
                'status' => 'not_found',
                'job_id' => $jobId,
            ], 404);
        }

        if ($job['status'] === 'complete') {
            $entry = $this->normalizeCacheEntry($cache->get($job['cache_key']));

            if ($entry === null) {
                return response()->json([
                    'async' => true,
                    'status' => 'failed',
                    'job_id' => $jobId,
                    'error' => 'Cached result missing after job completion.',
                ], 500);
            }

            return $this->jsonCachedResponse($entry['data'], 'fresh');
        }

        if ($job['status'] === 'failed') {
            return response()->json([
                'async' => true,
                'status' => 'failed',
                'job_id' => $jobId,
                'error' => $job['error'] ?? 'Query failed.',
            ], 500);
        }

        return response()->json([
            'async' => true,
            'status' => $job['status'],
            'job_id' => $jobId,
        ], 202);
    }

    public function markProcessing(string $jobId, string $statusKey, array $jobPayload): void
    {
        $cache = Cache::store('database');

        $jobPayload['status'] = 'processing';
        $cache->put($this->jobKey($jobId), $jobPayload, self::STATUS_TTL_SECONDS);
        $cache->put($statusKey, [
            'status' => 'processing',
            'job_id' => $jobId,
            'cache_key' => $jobPayload['cache_key'],
        ], self::STATUS_TTL_SECONDS);
    }

    public function markComplete(
        string $jobId,
        string $statusKey,
        string $cacheKey,
        mixed $data,
        int $cacheFreshSeconds,
        int $cacheStaleSeconds
    ): void {
        $cache = Cache::store('database');

        $cache->put($cacheKey, [
            'data' => $data,
            'cached_at' => time(),
            'fresh_ttl' => $cacheFreshSeconds,
            'stale_ttl' => $cacheStaleSeconds,
        ], $cacheStaleSeconds);

        $cache->put($this->jobKey($jobId), [
            'status' => 'complete',
            'cache_key' => $cacheKey,
        ], self::STATUS_TTL_SECONDS);

        $cache->put($statusKey, [
            'status' => 'complete',
            'job_id' => $jobId,
            'cache_key' => $cacheKey,
        ], self::STATUS_TTL_SECONDS);
    }

    public function markFailed(string $jobId, string $statusKey, string $cacheKey, string $error): void
    {
        $cache = Cache::store('database');

        $cache->put($this->jobKey($jobId), [
            'status' => 'failed',
            'cache_key' => $cacheKey,
            'error' => $error,
        ], self::STATUS_TTL_SECONDS);

        $cache->forget($statusKey);
    }

    public function jobKey(string $jobId): string
    {
        return 'global_async_job:'.$jobId;
    }

    public function statusKeyForCacheKey(string $cacheKey): string
    {
        return 'global_async_status:'.hash('sha256', $cacheKey);
    }

    private function dispatchBackgroundJob(
        string $cacheKey,
        string $handlerClass,
        string $handlerMethod,
        array $requestData,
        int $cacheFreshSeconds,
        int $cacheStaleSeconds
    ): string {
        $cache = Cache::store('database');
        $statusKey = $this->statusKeyForCacheKey($cacheKey);
        $existingStatus = $cache->get($statusKey);

        if ($existingStatus && in_array($existingStatus['status'], ['pending', 'processing'], true)) {
            return $existingStatus['job_id'];
        }

        $jobId = (string) Str::uuid();

        $statusPayload = [
            'status' => 'pending',
            'job_id' => $jobId,
            'cache_key' => $cacheKey,
        ];

        $jobPayload = [
            'status' => 'pending',
            'cache_key' => $cacheKey,
            'status_key' => $statusKey,
            'handler_class' => $handlerClass,
            'handler_method' => $handlerMethod,
            'request' => $requestData,
            'cache_fresh_seconds' => $cacheFreshSeconds,
            'cache_stale_seconds' => $cacheStaleSeconds,
        ];

        $cache->put($statusKey, $statusPayload, self::STATUS_TTL_SECONDS);
        $cache->put($this->jobKey($jobId), $jobPayload, self::STATUS_TTL_SECONDS);

        ProcessGlobalAsyncQueryJob::dispatch(
            $jobId,
            $statusKey,
            $cacheKey,
            $handlerClass,
            $handlerMethod,
            $requestData,
            $cacheFreshSeconds,
            $cacheStaleSeconds
        )->afterResponse();

        return $jobId;
    }

    private function normalizeCacheEntry(mixed $cached): ?array
    {
        if (! is_array($cached)) {
            return null;
        }

        if (isset($cached['data'], $cached['cached_at'], $cached['fresh_ttl'], $cached['stale_ttl'])) {
            return $cached;
        }

        return [
            'data' => $cached,
            'cached_at' => time(),
            'fresh_ttl' => PHP_INT_MAX,
            'stale_ttl' => PHP_INT_MAX,
        ];
    }

    private function getCacheAge(array $entry): int
    {
        return max(0, time() - (int) $entry['cached_at']);
    }

    private function jsonCachedResponse(mixed $data, string $cacheStatus): JsonResponse
    {
        return response()
            ->json($data)
            ->header('X-Global-Cache-Status', $cacheStatus);
    }
}
