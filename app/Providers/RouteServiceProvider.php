<?php

namespace App\Providers;

use App\Auth\ApiKeyContext;
use App\Auth\ApiKeyGuard;
use App\Services\ClientIpService;
use App\Support\ApiSpecConfig;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Keyless routes the desktop and electron uploaders call. Each has its own
     * per-IP limiter, so none of them belong in the shared per-key bucket.
     */
    private const UPLOADER_ROUTES = [
        'api.external.upload',
        'api.external.replays.fingerprint',
        'api.external.replays.parsed',
        'api.external.prematch',
        // The same four under their old paths, for clients that never updated.
        'api.legacy.*',
    ];

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($this->rateLimitKey($request));
        });

        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(40)->by($this->rateLimitKey($request));
        });

        // Per key, not per IP: two customers behind one address must not share a
        // bucket, and one customer's runaway loop must not throttle the other.
        RateLimiter::for('api-external', function (Request $request) {
            // The uploader's routes are anonymous and carry their own per-IP
            // limits. Leaving them in the anonymous bucket would cap an uploader
            // at 20 replays a minute.
            if ($request->routeIs(...self::UPLOADER_ROUTES)) {
                return Limit::none();
            }

            return Limit::perMinute($this->publicApiPerMinute($request))
                ->by($this->rateLimitKey($request));
        });

        // The uploader's keyless routes, per IP: they send no key to bucket by.
        // Ceilings live in `api.rate_limits.uploader`, which the spec also reads.
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(config('api.rate_limits.uploader.upload_per_minute'))
                ->by(ClientIpService::getClientIp($request));
        });

        RateLimiter::for('upload-daily', function (Request $request) {
            return Limit::perMinutes(1440, config('api.rate_limits.uploader.upload_per_day'))
                ->by(ClientIpService::getClientIp($request));
        });

        RateLimiter::for('replay-fingerprints', function (Request $request) {
            return Limit::perMinute(config('api.rate_limits.uploader.fingerprints_per_minute'))
                ->by(ClientIpService::getClientIp($request));
        });

        RateLimiter::for('replay-parsed', function (Request $request) {
            return Limit::perMinute(config('api.rate_limits.uploader.parsed_per_minute'))
                ->by(ClientIpService::getClientIp($request));
        });

        RateLimiter::for('prematch', function (Request $request) {
            return Limit::perMinute(config('api.rate_limits.uploader.prematch_per_minute'))
                ->by(ClientIpService::getClientIp($request));
        });

        /*
        | The docs "Try it" button. Every press runs a real API call, charged to the
        | account's own key and counted against its weekly quota, so the ceiling that
        | matters is already downstream — this only stops a script hammering the
        | portal endpoint.
        |
        | It used to borrow `contact`, which is three a minute. Correct for a contact
        | form; for a docs page it locked a reader out after three clicks, and the
        | 429 gave no hint which limit had been hit.
        |
        | Bucketed by portal account rather than IP: the route is behind
        | `ensureApiAccountAuth`, and two people testing from one office should not
        | share an allowance.
        */
        RateLimiter::for('docs-try', function (Request $request) {
            return Limit::perMinute(30)->by(
                Auth::guard('api_web')->id() ?? ClientIpService::getClientIp($request)
            );
        });

        // Battletag lookup: each search is a grouped scan over replay history.
        RateLimiter::for('battletag-search', function (Request $request) {
            return Limit::perMinute(20)->by($this->rateLimitKey($request));
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($this->rateLimitKey($request));
        });

        // Public replay search (POST api/v1/match/search). Each call can scan two seasons of replays
        RateLimiter::for('match-search', function (Request $request) {
            return Limit::perMinute(10)->by($this->rateLimitKey($request));
        });

        // Archive replay pages (GET Match/Single/{id}, replayID < max - 1,000,000)
        RateLimiter::for('old-replay', function (Request $request) {
            return Limit::perMinute(15)->by($this->rateLimitKey($request));
        });

        $this->routes(function () {
            // First, so nothing added to routes/api.php can shadow a legacy path by
            // accident. Domain-scoped, so it only exists once DNS points here.
            Route::middleware('api.external')
                ->domain(config('api.domain'))
                ->group(base_path('routes/api-legacy.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // One mount, one advertised URL. The API subdomain is deliberately not a
            // second way in: once DNS moves it redirects here, and only the legacy
            // uploader paths above still answer on it.
            Route::middleware('api.external')
                ->prefix(config('api.path'))
                ->group(base_path('routes/api-external.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Resolve the rate-limit bucket from the real client IP, not the load balancer.
     */
    protected function rateLimitKey(Request $request): string
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context !== null) {
            return 'apikey:'.$context->keyId;
        }

        if ($request->user()) {
            return 'user:'.$request->user()->id;
        }

        return ClientIpService::getClientIp($request);
    }

    /** Developer buys a higher ceiling; an unresolved key gets the anonymous one. */
    private function publicApiPerMinute(Request $request): int
    {
        $limits = config('api.rate_limits');
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context === null) {
            return $limits['anonymous'];
        }

        // Checked before the plan, and returned rather than maxed: a fan-out is
        // capped for everyone, including the tier whose plan limit is higher.
        if ($this->isBatchRequest($request)) {
            return $limits['batch'];
        }

        $planLimit = in_array(3, $context->planIds, true)
            ? $limits['developer']
            : $limits['default'];

        // Per-route floors raise the ceiling on the endpoints that need throughput
        // rather than on the plan as a whole, so a tier does not get to burst
        // through the expensive global queries on the strength of needing to read
        // replays quickly. See `config/api.php`.
        $routeLimit = $limits['routes'][$request->route()?->getName()] ?? 0;

        return max($planLimit, $routeLimit, $this->downloadGrantPerMinute($request, $context, $limits));
    }

    /**
     * The raised pace a bulk download grant carries, or zero for everyone else.
     *
     * Narrow on both axes deliberately. Only the download route, because the grant
     * is one endpoint turned up and has no business raising the pace on the
     * analytical queries. And only accounts actually holding it, so this is the same
     * answer as the weekly allowance rather than a second rule that can disagree
     * with it.
     *
     * Read from the flag's own plan id rather than a literal, so moving the plan is
     * a config edit in one place.
     *
     * @param  array<string, mixed>  $limits
     */
    private function downloadGrantPerMinute(Request $request, ApiKeyContext $context, array $limits): int
    {
        $planId = config('api_plans.additive_flags.do_approved');

        if ($planId === null || ! $request->routeIs('api.external.replay.download')) {
            return 0;
        }

        return in_array($planId, $context->planIds, true)
            ? (int) $limits['download_approved']
            : 0;
    }

    /**
     * Whether this request fans out into many queries rather than running one.
     *
     * Either because the endpoint always does, or because `group_by_map` asked it
     * to. See `api.rate_limits.batch`.
     */
    private function isBatchRequest(Request $request): bool
    {
        // Only where it is offered: elsewhere the request is refused before any
        // query runs, so it should not cost the caller the batch ceiling.
        if ($request->boolean('group_by_map')
            && ApiSpecConfig::declaresParameter($request->route()?->getName(), 'group_by_map')) {
            return true;
        }

        return in_array(
            $request->route()?->getName(),
            config('api.rate_limits.batch_routes', []),
            true
        );
    }
}
