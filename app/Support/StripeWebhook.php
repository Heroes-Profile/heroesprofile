<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Identifies Stripe's webhook delivery.
 *
 * Cashier registers that route from its own service provider, so it belongs to no
 * middleware group we control and has to be recognised by path. Two middlewares need
 * to agree on which request that is, and they must not drift apart — one exempts it
 * from rate limiting, the other refuses it when signature verification is not
 * configured. Disagreement between them would mean a throttled webhook or an unsigned
 * one being accepted.
 */
class StripeWebhook
{
    public static function matches(Request $request): bool
    {
        // Only the webhook. Cashier's payment confirmation page sits under the same
        // prefix but is user-facing, and neither rule should apply to it.
        return $request->isMethod('POST')
            && $request->is(trim((string) config('cashier.path', 'stripe'), '/').'/webhook');
    }
}
