<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the API key onto the request without rejecting anything.
 *
 * Deliberately not the `auth` middleware: a missing or bad key has to reach
 * EnforceApiQuota so the caller gets the documented JSON error envelope rather
 * than Laravel's default `{"message":"Unauthenticated."}`.
 *
 * Runs before the throttle so the limiter can bucket per key instead of per IP.
 */
class ResolveApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::guard('api_key')->user();

        return $next($request);
    }
}
