<?php

namespace App\Http\Middleware;

use App\Auth\ApiKeyGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * NGS endpoints are restricted to accounts granted NGS access. They carry no
 * weekly quota — access is the permission, and there is nothing to price because
 * it is not sold. Only the per-key throttle applies.
 *
 * NGS reads are metered like any other read, so upload is the only route behind
 * this.
 *
 * ->middleware('api.ngs')        either flag
 * ->middleware('api.ngs:upload') both flags
 */
class RequireNgsAccess
{
    public function handle(Request $request, Closure $next, ?string $mode = null): Response
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context === null) {
            return $this->error('unauthenticated', 'A valid API key is required.', 401);
        }

        // No quota middleware on these routes, so suspension is refused here.
        if ($context->isSuspended()) {
            return $this->error($context->suspensionCode(), $context->suspensionMessage(), 403);
        }

        $permitted = $mode === 'upload'
            ? $context->account->hasNgsUploadAccess()
            : $context->account->hasNgsAccess();

        if (! $permitted) {
            return $this->error(
                'ngs_access_required',
                'This endpoint is limited to accounts granted NGS access.',
                403
            );
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
