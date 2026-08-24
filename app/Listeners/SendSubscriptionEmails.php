<?php

namespace App\Listeners;

use App\Models\Api\ApiAccount;
use App\Notifications\Api\SubscriptionCancelled;
use App\Notifications\Api\SubscriptionStarted;
use App\Services\Api\PlanService;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Events\WebhookHandled;
use Throwable;

/**
 * Subscription mail, driven off Stripe rather than off our own controllers.
 *
 * The billing page is not the only place a subscription changes: cancelling stays on
 * the old site for the whole transition, and Spark never touches `cashier_subscriptions`.
 * Firing from BillingController would silently miss every one of those. Stripe sees all
 * of them, so Stripe is what we listen to.
 */
class SendSubscriptionEmails
{
    public function __construct(private readonly PlanService $plans) {}

    public function handle(WebhookHandled $event): void
    {
        $type = $event->payload['type'] ?? '';
        $object = $event->payload['data']['object'] ?? null;

        if (! is_array($object)) {
            return;
        }

        $account = $this->accountFor($object['customer'] ?? null);

        if ($account === null) {
            return;
        }

        $previous = $event->payload['data']['previous_attributes'] ?? [];

        // A failed send must not fail the webhook. Stripe retries on a non-2xx, and a
        // retry would re-run every handler — including sending this mail again.
        try {
            match ($type) {
                'customer.subscription.created' => $this->created($account, $object),
                'customer.subscription.updated' => $this->updated($account, $object, $previous),
                'customer.subscription.deleted' => $this->deleted($account, $object),
                default => null,
            };
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function created(ApiAccount $account, array $object): void
    {
        if (! in_array($object['status'] ?? null, ['active', 'trialing'], true)) {
            return;
        }

        $account->notify(new SubscriptionStarted($this->planName($object)));
    }

    private function updated(ApiAccount $account, array $object, array $previous): void
    {
        // Only when it just flipped on. Stripe sends `updated` for plenty of things,
        // and an unrelated change on an already-cancelled subscription still carries
        // `cancel_at_period_end: true`.
        if (($object['cancel_at_period_end'] ?? false) && array_key_exists('cancel_at_period_end', $previous)) {
            $account->notify(new SubscriptionCancelled(
                $this->planName($object),
                $this->endOfPeriod($object),
            ));

            return;
        }

        // A swap rewrites the items collection. Resumes also arrive as `updated`, and
        // deliberately get nothing.
        if (array_key_exists('items', $previous) || array_key_exists('plan', $previous)) {
            $account->notify(new SubscriptionStarted($this->planName($object), changed: true));
        }
    }

    private function deleted(ApiAccount $account, array $object): void
    {
        // A scheduled cancellation was already confirmed when it was scheduled. This
        // is the period actually running out, and a second email would only confuse.
        if ($object['cancel_at_period_end'] ?? false) {
            return;
        }

        $account->notify(new SubscriptionCancelled($this->planName($object), null));
    }

    private function accountFor(mixed $customerId): ?ApiAccount
    {
        if (! is_string($customerId) || $customerId === '') {
            return null;
        }

        return ApiAccount::where('stripe_id', $customerId)->first();
    }

    /** Tier name for the subscription's price, when we recognise it. */
    private function planName(array $object): ?string
    {
        $price = $object['items']['data'][0]['price']['id'] ?? null;

        if (! is_string($price)) {
            return null;
        }

        $planId = $this->plans->planIdForPrice($price);

        return $planId === null ? null : ($this->plans->all()[$planId]['name'] ?? null);
    }

    private function endOfPeriod(array $object): ?Carbon
    {
        $end = $object['current_period_end'] ?? null;

        return is_int($end) ? Carbon::createFromTimestamp($end) : null;
    }
}
