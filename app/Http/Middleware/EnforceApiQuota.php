<?php

namespace App\Http\Middleware;

use App\Auth\ApiKeyGuard;
use App\Models\Api\ApiEndpoint;
use App\Models\Api\ApiUsage;
use App\Support\ApiTermsDeadline;
use App\Support\ResponseBytes;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * The single enforcement point for the public API, replacing the old site's three
 * separate checks (guard, ValidateApiToken, and a per-controller call).
 *
 * Takes the registry key as a parameter: ->middleware('api.quota:heroes_stats')
 */
class EnforceApiQuota
{
    /**
     * Deliberately not `X-RateLimit-*`. That belongs to the per-minute throttle,
     * and Laravel's ThrottleRequests overwrites those headers with whichever limit
     * is tighter — which would hide the weekly allowance behind the per-minute one
     * and silently change what the header means as usage climbs.
     */
    private const HEADER_PREFIX = 'X-HP-Quota-';

    private const QUOTA_CACHE_SECONDS = 300;

    /** Where handle() leaves what terminate() needs. */
    private const TIMING_ATTRIBUTE = 'apiQuotaTiming';

    public function handle(Request $request, Closure $next, string $endpoint): Response
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context === null) {
            return $this->error('unauthenticated', 'A valid API key is required.', 401);
        }

        // Ahead of everything else on purpose — ahead of the admin bypass, and ahead
        // of the fixture path. A suspended account handed sample data would read the
        // suspension as an outage, and this has to be an answer they cannot mistake
        // for one. Its own code, too: `subscription_inactive` would tell an
        // integrator to go and check their billing.
        if ($context->isSuspended()) {
            return $this->error(
                $context->suspensionCode(),
                $context->suspensionMessage(),
                403,
                $endpoint
            );
        }

        // An admin exercising their grant is not metered: exercising every
        // endpoint to check it works would otherwise spend a real allowance, and
        // there is no one to bill. Entitlement, plan and quota are all skipped;
        // the route's rate limiter still applies.
        if ($context->account->actingAsAdmin()) {
            return $next($request);
        }

        // A key never visits the portal, so the terms page alone would let an
        // integration run on terms its owner never accepted.
        $termsVersion = config('api.terms_version');

        if ($termsVersion
            && $context->account->terms_version_accepted !== $termsVersion
            && ApiTermsDeadline::passed()) {
            return $this->error(
                'terms_not_accepted',
                'The API terms of service have changed. Sign in at '.url('/Api/Terms').' and accept them to carry on using the API.',
                403,
                $endpoint
            );
        }

        // Fixtures cost nothing: no registry lookup, no usage read or write. Only
        // the route's rate limiter applies.
        if ($context->servesFixtures()) {
            return $next($request);
        }

        // Their card is being charged for something we cannot price. Loud on both
        // sides: the caller gets an error rather than sample data, and this is a
        // data fault only we can fix.
        if ($context->subscriptionUnresolved) {
            Log::critical('API subscription resolved to no plan', [
                'account_id' => $context->account->id,
                'key_id' => $context->keyId,
                'endpoint' => $endpoint,
                'reason' => $context->unresolvedReason,
            ]);

            return $this->error(
                'plan_unresolved',
                // The same wording the portal shows them, so the integration's error
                // and the page they go on to read do not tell different stories.
                $context->unresolvedMessage().' This call was not charged against any allowance.',
                403,
                $endpoint
            );
        }

        if (! $context->isEntitled()) {
            return $this->error(
                'subscription_inactive',
                'Your subscription is not active.',
                403,
                $endpoint
            );
        }

        // After entitlement, so an account without a plan is told about the plan.
        if (! $context->account->hasProjectDetails() && ApiTermsDeadline::passed()) {
            return $this->error(
                'project_details_required',
                'Describe the project you use the API for at '.url('/Api/Account').' to carry on using the API.',
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
            )
                ->header('Retry-After', $this->secondsUntilReset($usage))
                ->header(self::HEADER_PREFIX.'Limit', $limit)
                ->header(self::HEADER_PREFIX.'Remaining', 0)
                ->header(self::HEADER_PREFIX.'Reset', $this->secondsUntilReset($usage));
        }

        // A cursor rather than a start time: it is moved forward once the response is
        // built, so terminate() adds only what happened after that — the send. Handed
        // over on the request because terminate() is passed no middleware parameters
        // and the container builds it a fresh instance.
        //
        // Measured from here rather than from LARAVEL_START. That constant belongs to
        // whichever request booted the process, which for an internally dispatched
        // call is the page that dispatched it — on the docs Try It console it would
        // have charged the whole page load to the endpoint.
        $request->attributes->set(self::TIMING_ATTRIBUTE, [
            'account_id' => $context->account->id,
            'endpoint' => $endpoint,
            'since' => microtime(true),
        ]);

        $response = $next($request);

        // Charged only for an answer: a 200, or a 202 whose job will deliver one.
        // A refused or failed call costs nothing.
        $charged = $response->isSuccessful();

        if ($charged) {
            DB::connection('heroesprofile_api')
                ->table('api_usage')
                ->where('api_account_id', $context->account->id)
                ->where('endpoint', $endpoint)
                ->increment('calls');
        }

        $this->recordEgress($context->account->id, $endpoint, $response);

        // Time spent building the answer, banked here rather than left to terminate().
        // Not every call reaches terminate(): the docs Try It console dispatches
        // through `app()->handle()` and stops there, so anything measured only on the
        // way out would read zero for it while calls and bytes climbed.
        $this->recordCompute($request, $context->account->id, $endpoint);

        // Set on the header bag rather than chained: `header()` is a Laravel response
        // helper, and the replay download answers with a Symfony StreamedResponse that
        // does not have it. Same for the fixture path, which answers a file.
        $response->headers->set(self::HEADER_PREFIX.'Limit', (string) $limit);
        $response->headers->set(self::HEADER_PREFIX.'Remaining', (string) max(0, $limit - ($usage->calls + ($charged ? 1 : 0))));
        $response->headers->set(self::HEADER_PREFIX.'Reset', (string) $this->secondsUntilReset($usage));

        return $response;
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
                'compute_ms' => 0,
                'window_started_at' => now(),
            ]);
        }

        if ($usage->windowHasExpired()) {
            $usage->forceFill([
                'calls' => 0,
                'egress_bytes' => 0,
                'compute_ms' => 0,
                'window_started_at' => now(),
            ])->save();
        }

        return $usage;
    }

    /**
     * Records how long the request held a container.
     *
     * Runs after the response has been sent, which is the whole point: a replay
     * download answers with a StreamedResponse, and its bytes do not move until
     * `send()`. Measured from inside handle() the download looks instant, when in
     * fact the container is pinned for as long as the client takes to receive the
     * file — and that time is what Cloud Run bills.
     *
     * Only requests that reached the endpoint are counted. A quota refusal, the
     * admin bypass and the fixture path all return before handle() sets the
     * attribute, so none of them leave a row here.
     */
    public function terminate(Request $request, Response $response): void
    {
        $timing = $request->attributes->get(self::TIMING_ATTRIBUTE);

        if (is_array($timing)) {
            $this->recordCompute($request, $timing['account_id'], $timing['endpoint']);
        }
    }

    /**
     * Banks the time since the cursor was last moved, and moves it to now.
     *
     * Called twice for a request served over HTTP — once when the response is built,
     * once when it has been sent — so the two add up to the whole without either
     * counting the other's share. On a JSON endpoint the second is sub-millisecond
     * and writes nothing; on the replay download it is the entire transfer.
     */
    private function recordCompute(Request $request, int $accountId, string $endpoint): void
    {
        $timing = $request->attributes->get(self::TIMING_ATTRIBUTE);

        if (! is_array($timing)) {
            return;
        }

        $now = microtime(true);
        $elapsedMs = (int) round(($now - $timing['since']) * 1000);

        $request->attributes->set(self::TIMING_ATTRIBUTE, ['since' => $now] + $timing);

        if ($elapsedMs <= 0) {
            return;
        }

        DB::connection('heroesprofile_api')
            ->table('api_usage')
            ->where('api_account_id', $accountId)
            ->where('endpoint', $endpoint)
            ->increment('compute_ms', $elapsedMs);
    }

    private function recordEgress(int $accountId, string $endpoint, Response $response): void
    {
        // Measured through ResponseBytes so the streamed replay download is counted
        // at all. Reading getContent() directly had it at zero on the one endpoint
        // whose egress is worth measuring.
        $bytes = ResponseBytes::of($response);

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
