<?php

namespace App\Http\Middleware;

use App\Support\StripeWebhook;
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
        // `v1/*` is the public API on its own subdomain, where the path carries no
        // `api/` prefix. It has its own per-key limiter and must not also land in
        // an IP bucket shared with every other caller behind the same address.
        // Stripe delivers from a small pool of IPs, so a burst of events shares one
        // bucket and trips the limiter. A 429 reads as a failed delivery: Stripe
        // retries for days and eventually disables the endpoint. Signature
        // verification is the control there, not rate limiting — enforced by
        // VerifyStripeWebhookSecretConfigured.
        if ($request->is('api/*', 'v1/*') || StripeWebhook::matches($request)) {
            return $next($request);
        }

        return app(ThrottleRequests::class)->handle($request, $next, 'global');
    }
}
