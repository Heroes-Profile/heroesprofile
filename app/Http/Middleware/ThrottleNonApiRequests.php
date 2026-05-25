<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleNonApiRequests
{
    /**
     * Apply the global rate limiter to web requests only.
     * API routes use the dedicated api limiter to avoid double-counting.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            return $next($request);
        }

        return app(ThrottleRequests::class)->handle($request, $next, 'global');
    }
}
