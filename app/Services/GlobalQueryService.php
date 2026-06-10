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

    public function handle(
        string $cacheKey,
        string $handlerClass,
        string $handlerMethod,
        array $requestData,
        int $cacheTtlSeconds
    ): JsonResponse {
        $cache = Cache::store('database');

        if ($cacheTtlSeconds > 0) {
            $cached = $cache->get($cacheKey);
            if ($cached !== null) {
                return response()->json($cached)
                    ->header('X-Global-Cache-Status', 'fresh')
                    ->header('X-Global-Async-Mode', 'cache-hit');
            }
        }

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
        $cache->put($cacheIndexKey, [
            'job_id' => $jobId,
            'status' => 'pending',
        ], self::STATUS_TTL_SECONDS);

        app(CloudTasksDispatcher::class)->dispatch($jobId);

        return $this->acceptedResponse($jobId, 'pending');
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

    public function runJob(string $jobId): void
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', '900');

        $cache = Cache::store('database');
        $job = $cache->get($this->jobKey($jobId));

        if (! is_array($job)) {
            throw new \RuntimeException("Job {$jobId} not found.");
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

            $this->markFailed($jobId, $job, $exception->getMessage());

            throw $exception;
        }
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

    private function markFailed(string $jobId, array $job, string $error): void
    {
        $cache = Cache::store('database');

        $job['status'] = 'failed';
        $job['error'] = $error;
        $cache->put($this->jobKey($jobId), $job, self::STATUS_TTL_SECONDS);
        $cache->forget($this->cacheIndexKey($job['cache_key']));
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
}
