<?php

namespace App\Listeners;

use App\Models\Api\ApiAccount;
use App\Services\Api\ApiKeyResolver;
use Laravel\Cashier\Events\WebhookHandled;

/**
 * Entitlement is cached alongside the key, so a subscription change made anywhere
 * other than our own billing page is invisible until the entry ages out.
 *
 * That is not a corner case during the transition: cancelling stays on the old site
 * for the whole six months, and Spark never touches `cashier_subscriptions`. Stripe's
 * webhook is the only way such a cancellation reaches this app, which makes this
 * listener the invalidation path for every one of them.
 */
class ClearApiEntitlementCache
{
    public function __construct(private readonly ApiKeyResolver $keys) {}

    public function handle(WebhookHandled $event): void
    {
        // Runs after Cashier has written the change, so the next resolve reads the
        // new state rather than re-caching the old one.
        if (! str_starts_with($event->payload['type'] ?? '', 'customer.subscription.')) {
            return;
        }

        $customerId = $event->payload['data']['object']['customer'] ?? null;

        if (! is_string($customerId) || $customerId === '') {
            return;
        }

        $account = ApiAccount::where('stripe_id', $customerId)->first();

        if ($account !== null) {
            $this->keys->forgetAccount($account->id);
        }
    }
}
