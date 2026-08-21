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
        // `v1/*` is the public API on its own subdomain, where the path carries no
        // `api/` prefix. It has its own per-key limiter and must not also land in
        // an IP bucket shared with every other caller behind the same address.
        if ($request->is('api/*', 'v1/*') || $this->isStripeWebhook($request)) {
            return $next($request);
        }

        return app(ThrottleRequests::class)->handle($request, $next, 'global');
    }

    /**
     * Stripe delivers from a small pool of IPs, so a burst of events shares one
     * bucket and trips the limiter. A 429 reads as a failed delivery: Stripe
     * retries for days and eventually disables the endpoint. Signature
     * verification is the control here, not rate limiting.
     *
     * Only the webhook is exempt — Cashier's payment confirmation page under the
     * same prefix is user-facing and stays throttled.
     */
    private function isStripeWebhook(Request $request): bool
    {
        return $request->isMethod('POST')
            && $request->is(trim(config('cashier.path', 'stripe'), '/').'/webhook');
    }
}
