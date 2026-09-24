<?php

namespace App\Http\Middleware;

use App\Auth\ApiKeyGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin-only API routes. Needs the grant and admin mode on — the same condition
 * that shows these routes on the docs page. Anyone else gets a 404, so the route
 * does not advertise itself.
 */
class RequireApiAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context === null) {
            return $this->error('unauthenticated', 'A valid API key is required.', 401);
        }

        if (! $context->account->actingAsAdmin()) {
            // Worded as the exception handler words an unknown route.
            return $this->error('not_found', 'Request could not be completed.', 404);
        }

        // No quota middleware on these routes, so suspension is refused here.
        if ($context->isSuspended()) {
            return $this->error($context->suspensionCode(), $context->suspensionMessage(), 403);
        }

        return $next($request);
    }

    private function error(string $code, string $message, int $status): Response
    {
        return response()->json([
            'error' => ['code' => $code, 'message' => $message],
        ], $status);
    }
}
