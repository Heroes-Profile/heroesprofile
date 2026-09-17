<?php

namespace App\Http\Middleware;

use App\Services\GlobalDataService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Player pages of private accounts are for their signed-in owner only; banned
 * accounts are for no one.
 */
class CheckIfPrivateProfilePage
{
    public function handle(Request $request, Closure $next): Response
    {
        // Route parameters, not $request['blizz_id']: that reads the query string
        // first, so ?blizz_id=<any public id> would have been checked instead.
        $blizzId = $request->route('blizz_id');
        $region = $request->route('region');

        if (app(GlobalDataService::class)->isHiddenFrom($blizzId, $region, Auth::user())) {
            return redirect('/');
        }

        return $next($request);
    }
}
