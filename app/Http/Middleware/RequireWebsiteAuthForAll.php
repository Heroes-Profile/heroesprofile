<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireWebsiteAuthForAll
{
    /**
     * Routes that must remain accessible without authentication
     * (auth flow, OAuth callbacks, ads.txt, etc.).
     */
    private const EXCLUDED_PATHS = [
        'Authenticate/Battlenet',
        'Battlenet/Logout',
        'redirect/authenticate/battlenet',
        'authenticate/battlenet/success',
        'Authenticate/Battlenet/Failed',
        'authenticate/patreon',
        'authenticate/patreon/success',
        'Authenticate/Patreon/Failed',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestPath = ltrim($request->path(), '/');

        foreach (self::EXCLUDED_PATHS as $path) {
            if (strcasecmp($requestPath, $path) === 0) {
                return $next($request);
            }
        }

        if (str_starts_with(strtolower($requestPath), 'ads.txt')) {
            return $next($request);
        }

        if (! Auth::check()) {
            return response()->view('loginRequired', [], 403);
        }

        return $next($request);
    }
}
