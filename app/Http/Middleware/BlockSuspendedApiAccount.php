<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops a suspended account doing anything in the portal that would hand it access
 * or take its money.
 *
 * Deliberately narrow. Signing in, reading why, seeing usage, downloading invoices
 * and cancelling a subscription all stay open: the point of leaving the door open is
 * that they can find out what happened and reply, and an account still being billed
 * has to be able to stop paying without going through us.
 */
class BlockSuspendedApiAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = Auth::guard('api_web')->user();

        if ($account === null || ! $account->isSuspended()) {
            return $next($request);
        }

        // Every route this guards answers JSON, so this is the shape the portal
        // already knows how to show.
        return response()->json([
            'error' => $account->isTerminated()
                ? 'This account has been closed. See the notice on your account page.'
                : 'This account is suspended. See the notice on your account page.',
        ], 403);
    }
}
