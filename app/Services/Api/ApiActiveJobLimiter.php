<?php

namespace App\Services\Api;

use App\Auth\ApiKeyContext;
use App\Auth\ApiKeyGuard;
use App\Services\GlobalQueryService;
use App\Support\DatabaseCacheReader;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Caps an API account's active jobs: cold jobs it has queued or running at once.
 *
 * Counted from the jobs' own records rather than a counter, so a job that
 * finishes, runs out of retries or ages out frees its slot without anything
 * having to release it.
 */
class ApiActiveJobLimiter
{
    private const LOCK_SECONDS = 15;

    private const LOCK_WAIT_SECONDS = 5;

    /** Outlives any job it lists. */
    private const LIST_TTL_SECONDS = 7200;

    /**
     * Runs `$start` if the account has a slot free, and refuses with a 429 if not.
     * Anything that isn't a keyed API call runs straight through.
     *
     * @param  callable(): array{0: string, 1: JsonResponse}  $start  Creates the job; returns its id and the response.
     */
    public function guard(callable $start): JsonResponse
    {
        $context = request()->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);
        $limit = $context instanceof ApiKeyContext ? $this->limitFor($context) : null;

        if ($limit === null) {
            return $start()[1];
        }

        $accountId = $context->account->id;
        $cache = Cache::store('database');

        try {
            return $cache->lock('api_active_jobs_lock:'.$accountId, self::LOCK_SECONDS)
                ->block(self::LOCK_WAIT_SECONDS, function () use ($start, $limit, $accountId, $cache) {
                    $active = $this->activeJobIds($accountId);

                    if (count($active) >= $limit) {
                        Log::info('API active job limit reached', [
                            'account_id' => $accountId,
                            'limit' => $limit,
                        ]);

                        return $this->refusal($limit);
                    }

                    [$jobId, $response] = $start();

                    $active[] = $jobId;
                    $cache->put($this->listKey($accountId), $active, self::LIST_TTL_SECONDS);

                    return $response;
                });
        } catch (LockTimeoutException) {
            return $this->refusal($limit);
        }
    }

    /**
     * The most generous cap across the account's plans, or null when it isn't
     * capped: admin mode, or holding any plan with no cap.
     */
    private function limitFor(ApiKeyContext $context): ?int
    {
        if ($context->account->actingAsAdmin() || $context->planIds === []) {
            return null;
        }

        $limits = config('api.active_jobs.limits');
        $limit = 0;

        foreach ($context->planIds as $planId) {
            if (! isset($limits[$planId])) {
                return null;
            }

            $limit = max($limit, (int) $limits[$planId]);
        }

        return $limit;
    }

    /** @return array<int, string> */
    private function activeJobIds(int $accountId): array
    {
        $jobIds = Cache::store('database')->get($this->listKey($accountId));

        if (! is_array($jobIds) || $jobIds === []) {
            return [];
        }

        $queries = app(GlobalQueryService::class);
        $keys = array_map(fn (string $jobId) => $queries->jobKey($jobId), $jobIds);
        $jobs = DatabaseCacheReader::many($keys);

        return array_values(array_filter(
            $jobIds,
            fn (string $jobId) => $queries->isOpen($jobs[$queries->jobKey($jobId)] ?? null)
        ));
    }

    private function refusal(int $limit): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'too_many_active_jobs',
                'message' => 'Your account already has '.$limit.' active jobs, the most your plan allows.'
                    .' Collect one from /v1/jobs before starting another. Cached answers are not affected.',
            ],
        ], 429)
            ->header('Retry-After', (string) config('api.active_jobs.retry_after'))
            ->header('X-HP-Active-Jobs-Limit', (string) $limit);
    }

    private function listKey(int $accountId): string
    {
        return 'api_active_jobs:'.$accountId;
    }
}
