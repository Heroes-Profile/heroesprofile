<?php

namespace App\Providers;

use App\Auth\ApiKeyGuard;
use App\Services\ClientIpService;
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

        // Replay ingestion, per IP: the uploaders send no key to bucket by. Both
        // ceilings are the ones the old upload route carried.
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(60)->by(ClientIpService::getClientIp($request));
        });

        RateLimiter::for('upload-daily', function (Request $request) {
            return Limit::perMinutes(1440, 20000)->by(ClientIpService::getClientIp($request));
        });

        // The uploader's remaining calls, at the ceilings their old routes had.
        // The fingerprint check is generous because the client makes one per
        // replay before deciding whether to upload at all.
        RateLimiter::for('replay-fingerprints', function (Request $request) {
            return Limit::perMinute(5000)->by(ClientIpService::getClientIp($request));
        });

        RateLimiter::for('replay-parsed', function (Request $request) {
            return Limit::perMinute(60)->by(ClientIpService::getClientIp($request));
        });

        RateLimiter::for('prematch', function (Request $request) {
            return Limit::perMinute(120)->by(ClientIpService::getClientIp($request));
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

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($this->rateLimitKey($request));
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

        return in_array(3, $context->planIds, true)
            ? $limits['developer']
            : $limits['default'];
    }
}
