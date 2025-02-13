<?php

namespace App\Http\Middleware;

use App\Models\BattlenetAccount;
use App\Models\PatreonAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfPatreonSupporter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $user = BattlenetAccount::find(1);
        // Auth::login($user);

        if (Auth::check()) {
            $user = Auth::user();
            $patreonUser = PatreonAccount::where('battlenet_accounts_id', $user->battlenet_accounts_id)->first();

            if ($patreonUser) {
                if ($patreonUser->site_flair == 1) {
                    session(['patreonSubscriberSiteFlair' => true]);
                }

                if ($patreonUser->ad_free == 1) {
                    session(['patreonSubscriberAdFree' => true]);
                }
            }

            if ($user->flair_adfree_override == 1) {
                session(['patreonSubscriberAdFree' => true]);
                session(['patreonSubscriberSiteFlair' => true]);
            }
        }

        return $next($request);
    }
}
