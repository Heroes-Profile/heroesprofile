<?php

namespace App\Http\Middleware;

use App\Auth\ApiKeyGuard;
use App\Services\Api\PoisonPill;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Marks global stats for accounts flagged in the admin console, and logs each
 * marked value beside the real one. See PoisonPill.
 *
 * Listed after the fixture and quota middleware, so it only ever sees live
 * output, and ConvertResponseToCsv still runs after it.
 */
class PoisonApiResponse
{
    /** Where a job's original call is kept, so its result logs under that call. */
    private const JOB_KEY = 'api_poison_job:';

    private const JOB_SECONDS = 86400;

    /** Never logged: the key itself, and the output format. */
    private const IGNORED_PARAMS = ['api_token', 'mode'];

    public function __construct(private readonly PoisonPill $pill) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context === null || ! $context->account->isPoisoned() || ! $response instanceof JsonResponse) {
            return $response;
        }

        $accountId = $context->account->id;

        if ($response->getStatusCode() === 202) {
            $this->rememberJob($request, $response);

            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $marks = [];
        $response->setData($this->pill->apply($response->getData(true), $accountId, $marks));

        if ($marks !== []) {
            $this->log($request, $accountId, $marks);
        }

        return $response;
    }

    private function rememberJob(Request $request, JsonResponse $response): void
    {
        $jobId = $response->getData(true)['job_id'] ?? null;

        if (is_string($jobId)) {
            Cache::put(self::JOB_KEY.$jobId, $this->call($request), self::JOB_SECONDS);
        }
    }

    /** @return array{endpoint: string, params: array} */
    private function call(Request $request): array
    {
        $jobId = $request->route('jobId');

        if ($jobId !== null && is_array($original = Cache::get(self::JOB_KEY.$jobId))) {
            return $original;
        }

        $path = trim($request->path(), '/');
        $prefix = trim((string) config('api.path'), '/');

        if (str_starts_with($path, $prefix)) {
            $path = trim(substr($path, strlen($prefix)), '/');
        }

        $params = array_diff_key($request->query(), array_flip(self::IGNORED_PARAMS));
        ksort($params);

        return ['endpoint' => $path, 'params' => $params];
    }

    /** A logging failure must never cost the caller their response. */
    private function log(Request $request, int $accountId, array $marks): void
    {
        try {
            ['endpoint' => $endpoint, 'params' => $params] = $this->call($request);

            $json = json_encode($params);
            $hash = hash('sha256', $endpoint.'|'.$json);
            $now = now();

            $rows = [];

            // The same row can appear twice in one payload (ally and enemy lists),
            // and an upsert batch cannot hit one unique key twice.
            foreach ($marks as $mark) {
                $rows[$mark['identity'].'|'.$mark['real']] = [
                    'api_account_id' => $accountId,
                    'endpoint' => mb_substr($endpoint, 0, 100),
                    'params' => $json,
                    'params_hash' => $hash,
                    'identity' => mb_substr($mark['identity'], 0, 100),
                    'label' => $mark['label'],
                    'real_value' => $mark['real'],
                    'sent_value' => $mark['sent'],
                    'times_sent' => 1,
                    'first_sent_at' => $now,
                    'last_sent_at' => $now,
                ];
            }

            DB::connection('heroesprofile_api')->table('api_poison_log')->upsert(
                array_values($rows),
                ['api_account_id', 'params_hash', 'identity', 'real_value'],
                ['times_sent' => DB::raw('times_sent + 1'), 'last_sent_at' => $now]
            );
        } catch (Throwable $e) {
            report($e);
        }
    }
}
