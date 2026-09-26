<?php

namespace App\Http\Middleware;

use App\Services\Api\UsageService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Holds an account that holds a plan on its account page until it has described
 * its project. Accounts without a plan are asked when they go to subscribe instead.
 */
class RequireApiProjectDetails
{
    /** Where the form is, and billing, so a held account can still cancel. */
    private const ALLOWED = ['Api/Account', 'Api/Account/Billing'];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = Auth::guard('api_web')->user();

        if (! $account || $account->isAdmin() || $account->hasProjectDetails()) {
            return $next($request);
        }

        // Only normal navigation, for the same reason as RequireApiTermsAcceptance.
        if (! $request->isMethod('GET') || $request->expectsJson() || $request->is(...self::ALLOWED)) {
            return $next($request);
        }

        if (app(UsageService::class)->planIdsFor($account) === []) {
            return $next($request);
        }

        return redirect('/Api/Account#project');
    }
}
