<?php

namespace App\Providers;

use App\Rules\BattletagInputProhibitCharacters;
use App\Services\GlobalDataService;
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
            // These cookies are plain JSON the browser controls; anything malformed is dropped rather than rendered.
            $globalDataService = app(GlobalDataService::class);
            $regions = $globalDataService->getRegionIDtoString();
            $searchAccount = function ($raw) use ($regions, $globalDataService) {
                $account = $raw ? json_decode($raw, true) : null;

                $valid = is_array($account)
                    && (new BattletagInputProhibitCharacters)->passes('battletag', $account['battletag'] ?? null)
                    && ctype_digit((string) ($account['blizz_id'] ?? ''));

                if (! $valid) {
                    return null;
                }

                // Esports players have no region; esport_nav is the league and its links
                if (isset($account['league'])) {
                    $account['esport_nav'] = $globalDataService->esportNavLeague($account);

                    return $account['esport_nav'] ? $account : null;
                }

                return isset($regions[(int) ($account['region'] ?? 0)]) ? $account : null;
            };

            $main_search_account = $searchAccount(request()->cookie('main_search_account'));
            $alt_search_account1 = $searchAccount(request()->cookie('alt_search_account1'));
            $alt_search_account2 = $searchAccount(request()->cookie('alt_search_account2'));
            $alt_search_account3 = $searchAccount(request()->cookie('alt_search_account3'));

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
