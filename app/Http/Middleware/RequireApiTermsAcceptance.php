<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Holds an account on the terms page until it has accepted the current version.
 * Bumping `api.terms_version` is what re-triggers this for everyone.
 */
class RequireApiTermsAcceptance
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = Auth::guard('api_web')->user();

        if (! $account) {
            return $next($request);
        }

        // Only normal navigation — a form post or XHR redirected to the terms page
        // would fail in a way the caller cannot make sense of.
        if (! $request->isMethod('GET') || $request->expectsJson()) {
            return $next($request);
        }

        $current = config('api.terms_version');

        if (! $current || $account->terms_version_accepted === $current) {
            return $next($request);
        }

        // Kept rather than flashed — it has to survive reading the terms page.
        $request->session()->put('terms_intended', $request->fullUrl());

        return redirect('/Api/Terms');
    }
}
