<?php

namespace App\Support;

use App\Services\GlobalQueryService;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Which API accounts were handed a job id, so `jobs/{jobId}` answers only them.
 *
 * One marker per account rather than one owner per job: a pending job is handed
 * to every caller asking the same question, so the second caller must not lock
 * out the first. Same store and lifetime as the job record itself.
 */
class ApiJobOwner
{
    private const PREFIX = 'api_job_owner:';

    public static function remember(Response $response, int $accountId): void
    {
        if ($response->getStatusCode() !== 202) {
            return;
        }

        $jobId = json_decode((string) $response->getContent(), true)['job_id'] ?? null;

        if (is_string($jobId) && $jobId !== '') {
            Cache::store('database')->put(self::key($jobId, $accountId), true, GlobalQueryService::STATUS_TTL_SECONDS);
        }
    }

    public static function owns(string $jobId, int $accountId): bool
    {
        return Cache::store('database')->has(self::key($jobId, $accountId));
    }

    private static function key(string $jobId, int $accountId): string
    {
        return self::PREFIX.$jobId.':'.$accountId;
    }
}
