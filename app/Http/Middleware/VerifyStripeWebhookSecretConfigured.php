<?php

namespace App\Http\Middleware;

use App\Support\StripeWebhook;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses Stripe webhooks when there is no secret to verify them against.
 *
 * Cashier attaches `VerifyWebhookSignature` only when `cashier.webhook.secret` is
 * truthy. A blank one therefore does not fail — it silently accepts **any** POST and
 * answers 200, which is indistinguishable from a working endpoint until someone
 * notices that anyone can forge `customer.subscription.updated` and grant themselves
 * a plan, or cancel somebody else's.
 *
 * Failing closed here is deliberate. Refusing to boot the whole application over a
 * missing Stripe secret would take the statistics site down with it, and the site has
 * nothing to do with billing.
 */
class VerifyStripeWebhookSecretConfigured
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! StripeWebhook::matches($request)) {
            return $next($request);
        }

        if (filled(config('cashier.webhook.secret'))) {
            return $next($request);
        }

        Log::critical('Stripe webhook refused: STRIPE_WEBHOOK_SECRET is empty, so Cashier would accept unsigned events.', [
            'ip' => $request->ip(),
        ]);

        // 5xx so Stripe keeps retrying: the events are held rather than lost while
        // the secret is put in place. Stripe disables an endpoint that fails for
        // days, which is the right amount of pressure for a config fault.
        return response('Stripe webhook signature verification is not configured.', 500);
    }
}
