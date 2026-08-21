<?php

namespace App\Providers;

use App\Auth\ApiKeyGuard;
use App\Services\ClientIpService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
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
        RateLimiter::for('api-public', function (Request $request) {
            // Ingestion is anonymous and carries its own per-IP limits. Leaving it
            // in the anonymous bucket would cap an uploader at 20 replays a minute.
            if ($request->routeIs('api.public.upload')) {
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

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($this->rateLimitKey($request));
        });

        // Archive replay pages (GET Match/Single/{id}, replayID < max - 1,000,000)
        RateLimiter::for('old-replay', function (Request $request) {
            return Limit::perMinute(15)->by($this->rateLimitKey($request));
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api.public')
                ->domain(config('api.domain'))
                ->prefix('v1')
                ->group(base_path('routes/api-public.php'));

            // Same routes, reachable before DNS moves to this app.
            Route::middleware('api.public')
                ->prefix(config('api.path'))
                ->group(base_path('routes/api-public.php'));

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
