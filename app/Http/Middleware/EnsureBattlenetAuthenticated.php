<?php

namespace App\Http\Middleware;

use App\Models\BattlenetAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBattlenetAuthenticated
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

        // Local testing only: act as this blizz ID without signing in. Never applies outside APP_ENV=local.
        if (app()->environment('local') && ! Auth::check() && ($blizzId = config('analytics.local_test_blizz_id'))) {
            Auth::setUser((new BattlenetAccount)->forceFill([
                'blizz_id' => (int) $blizzId,
                'region' => (int) config('analytics.local_test_region'),
                'battletag' => 'LocalTest',
            ]));
        }

        if (! Auth::check()) {
            return redirect()->guest('/Authenticate/Battlenet');
        }

        return $next($request);
    }
}
