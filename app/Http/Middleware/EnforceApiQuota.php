<?php

namespace App\Http\Middleware;

use App\Auth\ApiKeyGuard;
use App\Models\Api\ApiEndpoint;
use App\Models\Api\ApiUsage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * The single enforcement point for the public API, replacing the old site's three
 * separate checks (guard, ValidateApiToken, and a per-controller call).
 *
 * Takes the registry key as a parameter: ->middleware('api.quota:heroes_stats')
 */
class EnforceApiQuota
{
    private const QUOTA_CACHE_SECONDS = 300;

    public function handle(Request $request, Closure $next, string $endpoint): Response
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context === null) {
            return $this->error('unauthenticated', 'A valid API key is required.', 401);
        }

        // Fixtures cost nothing: no registry lookup, no usage read or write. Only
        // the route's rate limiter applies.
        if ($context->servesFixtures()) {
            return $next($request);
        }

        if (! $context->isEntitled()) {
            return $this->error(
                'subscription_inactive',
                'Your subscription is not active.',
                403,
                $endpoint
            );
        }

        $limit = $this->limitFor($endpoint, $context->planIds);

        if ($limit === null || $limit === 0) {
            return $this->error(
                'endpoint_not_in_plan',
                'Your plan does not include this endpoint.',
                403,
                $endpoint
            );
        }

        $usage = $this->currentUsage($context->account->id, $endpoint);

        if ($usage->calls >= $limit) {
            return $this->error(
                'quota_exceeded',
                'Weekly limit of '.number_format($limit).' calls reached for this endpoint.',
                429,
                $endpoint
            )->header('Retry-After', $this->secondsUntilReset($usage));
        }

        DB::connection('heroesprofile_api')
            ->table('api_usage')
            ->where('api_account_id', $context->account->id)
            ->where('endpoint', $endpoint)
            ->increment('calls');

        $response = $next($request);

        $this->recordEgress($context->account->id, $endpoint, $response);

        return $response
            ->header('X-RateLimit-Limit', $limit)
            ->header('X-RateLimit-Remaining', max(0, $limit - ($usage->calls + 1)))
            ->header('X-RateLimit-Reset', $this->secondsUntilReset($usage));
    }

    /**
     * The most generous allowance across every plan the account holds, so buying a
     * tier on top of a comped grant can only ever help.
     *
     * @param  array<int, int>  $planIds
     */
    private function limitFor(string $endpoint, array $planIds): ?int
    {
        if ($planIds === []) {
            return null;
        }

        sort($planIds);

        $limit = Cache::remember(
            'api_quota:'.$endpoint.':'.implode(',', $planIds),
            self::QUOTA_CACHE_SECONDS,
            function () use ($endpoint, $planIds) {
                $max = ApiEndpoint::query()
                    ->join('api_endpoint_quotas as q', 'q.endpoint_id', '=', 'api_endpoints.endpoint_id')
                    ->where('api_endpoints.endpoint', $endpoint)
                    ->whereIn('q.subscription_plan', $planIds)
                    ->max('q.calls_per_week');

                return $max === null ? false : (int) $max;
            }
        );

        return $limit === false ? null : $limit;
    }

    /** Creates the row on first use and restarts the window once it has aged out. */
    private function currentUsage(int $accountId, string $endpoint): ApiUsage
    {
        $usage = ApiUsage::where('api_account_id', $accountId)
            ->where('endpoint', $endpoint)
            ->first();

        if ($usage === null) {
            return ApiUsage::create([
                'api_account_id' => $accountId,
                'endpoint' => $endpoint,
                'calls' => 0,
                'egress_bytes' => 0,
                'window_started_at' => now(),
            ]);
        }

        if ($usage->windowHasExpired()) {
            $usage->forceFill([
                'calls' => 0,
                'egress_bytes' => 0,
                'window_started_at' => now(),
            ])->save();
        }

        return $usage;
    }

    private function recordEgress(int $accountId, string $endpoint, Response $response): void
    {
        $bytes = strlen((string) $response->getContent());

        if ($bytes <= 0) {
            return;
        }

        DB::connection('heroesprofile_api')
            ->table('api_usage')
            ->where('api_account_id', $accountId)
            ->where('endpoint', $endpoint)
            ->increment('egress_bytes', $bytes);
    }

    private function secondsUntilReset(ApiUsage $usage): int
    {
        $resetsAt = ($usage->window_started_at ?? now())->copy()->addDays(ApiUsage::WINDOW_DAYS);

        return max(0, now()->diffInSeconds($resetsAt, false));
    }

    private function error(string $code, string $message, int $status, ?string $endpoint = null): Response
    {
        $error = ['code' => $code, 'message' => $message];

        if ($endpoint !== null) {
            $error['endpoint'] = $endpoint;
        }

        return response()->json(['error' => $error], $status);
    }
}
