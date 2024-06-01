<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $main_search_account = request()->cookie('main_search_account');
            $alt_search_account1 = request()->cookie('alt_search_account1');
            $alt_search_account2 = request()->cookie('alt_search_account2');
            $alt_search_account3 = request()->cookie('alt_search_account3');

            if ($main_search_account) {
                $main_search_account = json_decode($main_search_account, true);
            }

            if ($alt_search_account1) {
                $alt_search_account1 = json_decode($alt_search_account1, true);
            }

            if ($alt_search_account2) {
                $alt_search_account2 = json_decode($alt_search_account2, true);
            }

            if ($alt_search_account3) {
                $alt_search_account3 = json_decode($alt_search_account3, true);
            }

            if (Auth::check()) {
                $user = Auth::user();
                $main_search_account['battletag'] = explode('#', $user['battletag'])[0];
                $main_search_account['battletag_full'] = $user['battletag'];
                $main_search_account['blizz_id'] = $user['blizz_id'];
                $main_search_account['region'] = $user['region'];
            }
            $view->with('main_search_account', $main_search_account);
            $view->with('alt_search_account1', $alt_search_account1);
            $view->with('alt_search_account2', $alt_search_account2);
            $view->with('alt_search_account3', $alt_search_account3);
        });
    }
}
