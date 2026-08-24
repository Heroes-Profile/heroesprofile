<?php

namespace App\Listeners;

use App\Mail\Api\SubscriptionActivity;
use App\Models\Api\ApiAccount;
use App\Notifications\Api\SubscriptionCancelled;
use App\Notifications\Api\SubscriptionStarted;
use App\Services\Api\PlanService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
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

        $this->tellAdmin('Subscription started', $account, $this->planName($object), null);

        $account->notify(new SubscriptionStarted($this->planName($object)));
    }

    private function updated(ApiAccount $account, array $object, array $previous): void
    {
        // Only when it just flipped on. Stripe sends `updated` for plenty of things,
        // and an unrelated change on an already-cancelled subscription still carries
        // `cancel_at_period_end: true`.
        if (($object['cancel_at_period_end'] ?? false) && array_key_exists('cancel_at_period_end', $previous)) {
            $endsAt = $this->endOfPeriod($object);

            $this->tellAdmin(
                'Subscription cancelled',
                $account,
                $this->planName($object),
                $endsAt?->toDayDateTimeString().' UTC',
            );

            $account->notify(new SubscriptionCancelled($this->planName($object), $endsAt));

            return;
        }

        // A swap rewrites the items collection. Resumes also arrive as `updated`, and
        // deliberately get nothing.
        if (array_key_exists('items', $previous) || array_key_exists('plan', $previous)) {
            $this->tellAdmin('Plan changed', $account, $this->planName($object), null);

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

        $this->tellAdmin('Subscription ended', $account, $this->planName($object), null);

        $account->notify(new SubscriptionCancelled($this->planName($object), null));
    }

    /**
     * Reported separately from the customer's own mail so one failing address cannot
     * swallow the other. Silent when no address is configured.
     */
    private function tellAdmin(string $event, ApiAccount $account, ?string $plan, ?string $endsAt): void
    {
        $address = config('mail.admin_address');

        if (! $address) {
            return;
        }

        // Caught here rather than by the caller: a bad admin address must not stop the
        // customer's own mail, and the customer's must not stop this one.
        try {
            Mail::to($address)->send(new SubscriptionActivity(
                $event,
                (string) $account->name,
                (string) $account->email,
                $plan,
                $endsAt,
            ));
        } catch (Throwable $e) {
            report($e);
        }
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
