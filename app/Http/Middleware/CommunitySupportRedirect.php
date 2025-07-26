<?php

namespace App\Http\Middleware;

use App\Models\PatreonAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CommunitySupportRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // List of paths to exclude (case-insensitive)
        $excludedPaths = [
            'Authenticate/Battlenet',
            'Battlenet/Logout',
            'redirect/authenticate/battlenet',
            'authenticate/battlenet/success',
            'Authenticate/Battlenet/Failed',
            'authenticate/patreon',
            'authenticate/patreon/success',
            'Authenticate/Patreon/Failed',
            'Community/Support',
            'Profile/Settings',
        ];

        // Normalize and compare against request path (case-insensitive)
        $requestPath = ltrim($request->path(), '/'); // strip leading slash
        foreach ($excludedPaths as $path) {
            if (strcasecmp($requestPath, $path) === 0) {
                return $next($request);
            }
        }

        // Continue with normal logic
        $patreonUser = null;
        if (Auth::check()) {
            $user = Auth::user();
            $patreonUser = PatreonAccount::where('battlenet_accounts_id', $user->battlenet_accounts_id)->first();
        }

        if ($patreonUser && ($patreonUser->site_flair == 1 || $patreonUser->ad_free == 1)) {
            return $next($request);
        }

        return redirect('/Community/Support');
    }
}
