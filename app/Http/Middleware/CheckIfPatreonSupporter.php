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

            // The account's event flair in one query: it can grant a stretch of ad-free (e.g. 3 months for
            // finding the Xal'atath eye), and the nav's user menu lists the eye.
            $flair = BattlenetAccountFlair::where('battlenet_accounts_id', $user->battlenet_accounts_id)
                ->get(['flair', 'ad_free_until']);

            $adFree = ($patreonUser && $patreonUser->ad_free == 1)
                || $override
                || $flair->contains(fn ($row) => $row->ad_free_until && $row->ad_free_until->isFuture());

            // Flair and ad-free are separate: the Patreon star is for Patreon subscribers only. The override and the
            // eye's time only ever give ad-free
            $siteFlair = $patreonUser && $patreonUser->site_flair == 1;

            // Written both ways every request, so a lapsed pledge or expired flair takes effect immediately.
            session([
                'patreonSubscriberAdFree' => $adFree,
                'patreonSubscriberSiteFlair' => $siteFlair,
                'voidEyeFlair' => $flair->contains('flair', BattlenetAccountFlair::XALATATH_EYE),
            ]);
        }

        return $next($request);
    }
}
