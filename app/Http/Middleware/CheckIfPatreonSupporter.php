<?php

namespace App\Http\Middleware;

use App\Models\BattlenetAccount;
use App\Models\BattlenetAccountFlair;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfPatreonSupporter
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $user = BattlenetAccount::find(1);
        // Auth::login($user);

        if (Auth::check()) {
            $user = Auth::user();
            // Eager-loaded on the account, so no second query.
            $patreonUser = $user->patreonAccount;
            $override = $user->flair_adfree_override == 1;

            // Event flair can grant a stretch of ad-free (e.g. 3 months for finding the Xal'atath eye).
            $adFree = ($patreonUser && $patreonUser->ad_free == 1)
                || $override
                || BattlenetAccountFlair::where('battlenet_accounts_id', $user->battlenet_accounts_id)
                    ->where('ad_free_until', '>', now())
                    ->exists();

            $siteFlair = ($patreonUser && $patreonUser->site_flair == 1) || $override;

            // Written both ways every request, so a lapsed pledge or expired flair takes effect immediately.
            session([
                'patreonSubscriberAdFree' => $adFree,
                'patreonSubscriberSiteFlair' => $siteFlair,
            ]);
        }

        return $next($request);
    }
}
