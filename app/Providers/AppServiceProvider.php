<?php

namespace App\Providers;

use App\Auth\ApiKeyGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Sanctum::ignoreMigrations();
        $maxExecutionTime = env('PHP_MAX_EXECUTION_TIME', 300);
        $this->app->bind(GlobalDataService::class, function ($app) {
            return new GlobalDataService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        Auth::viaRequest('api_key', fn ($request) => app(ApiKeyGuard::class)($request));
    }
}
