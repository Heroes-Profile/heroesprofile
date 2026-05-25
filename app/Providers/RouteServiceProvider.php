<?php

namespace App\Providers;

use App\Services\WhitelistedIPsService;
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
            return Limit::perMinute(180)->by($this->rateLimitKey($request));
        });

        // Page navigations and other non-API requests
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(120)->by($this->rateLimitKey($request));
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

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Resolve the rate-limit bucket from the real client IP, not the load balancer.
     */
    protected function rateLimitKey(Request $request): string
    {
        if ($request->user()) {
            return 'user:'.$request->user()->id;
        }

        return WhitelistedIPsService::getClientIp($request);
    }
}
