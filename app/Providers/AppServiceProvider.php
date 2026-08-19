<?php

namespace App\Providers;

use App\Auth\ApiKeyGuard;
use App\Models\Api\ApiAccount;
use App\Models\Api\CashierSubscription;
use App\Models\Api\CashierSubscriptionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
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

        // Cashier bills API accounts, and its tables live under non-default names
        // so the old API site keeps using `subscriptions` until the cutover.
        Cashier::useCustomerModel(ApiAccount::class);
        Cashier::useSubscriptionModel(CashierSubscription::class);
        Cashier::useSubscriptionItemModel(CashierSubscriptionItem::class);
    }
}
