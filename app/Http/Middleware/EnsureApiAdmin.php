<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the portal's admin pages.
 *
 * Checks the grant, not whether it is currently being exercised: an admin who has
 * switched admin mode off to see what a customer sees should not lose the door
 * back. Only the behaviours that change what data a call returns — quota, keys,
 * the migration gate — follow `admin_mode`.
 */
class EnsureApiAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = Auth::guard('api_web')->user();

        if ($account === null) {
            return redirect('/Api/Login');
        }

        if (! $account->isAdmin()) {
            return response()->view('errors.403', [
                'message' => 'That page is for administrators.',
            ], 403);
        }

        return $next($request);
    }
}
