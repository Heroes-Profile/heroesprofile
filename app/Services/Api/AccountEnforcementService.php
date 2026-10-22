<?php

namespace App\Services\Api;

use App\Models\Api\ApiAccount;
use App\Models\Api\ApiAccountAction;
use App\Notifications\Api\AccountInfoRequested;
use App\Notifications\Api\AccountReinstated;
use App\Notifications\Api\AccountSuspended;
use App\Notifications\Api\AccountTerminated;
use App\Notifications\Api\AccountWarned;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Cashier;
use Throwable;

/**
 * The one place an account's standing changes.
 *
 * Three rungs, each heavier than the last:
 *
 * - **warn** — nothing about access changes. A banner and an email, and a row
 *   proving we said so. This is the rung for a licence condition being missed
 *   rather than abused: attribution, caching, that sort of thing.
 * - **suspend** — reversible. Every key stops working on both sites; the
 *   subscription keeps running, because a suspension is meant to last days and
 *   making them re-enter payment details to come back turns a warning into
 *   something much larger than intended. Optionally it cancels the subscription
 *   too and refunds the unused time, for someone who is not coming back to it.
 * - **terminate** — permanent, and the subscription is cancelled immediately.
 *   No refund (terms §9), but charging again for something they can never use is
 *   indefensible and invites a chargeback.
 *
 * Nothing here escalates on its own. Every rung is a person pressing a button.
 */
class AccountEnforcementService
{
    public function __construct(private readonly ApiKeyResolver $keys) {}

    /**
     * Notice, not enforcement. Their integration keeps working throughout — which
     * is the point: the cost to them is having to read it.
     */
    public function warn(
        ApiAccount $account,
        string $reason,
        ?string $notes = null,
        ?Carbon $respondBy = null,
        ?int $performedBy = null,
    ): ApiAccountAction {
        $action = ApiAccountAction::create([
            'api_account_id' => $account->id,
            'action' => ApiAccountAction::WARN,
            'reason' => $reason,
            'notes' => $notes,
            'respond_by' => $respondBy,
            'performed_by' => $performedBy,
        ]);

        $this->notify($account, new AccountWarned($reason, $respondBy));

        return $action;
    }

    public function suspend(
        ApiAccount $account,
        string $reason,
        ?string $notes = null,
        ?int $performedBy = null,
    ): ApiAccountAction {
        $action = $this->withdraw($account, ApiAccount::SUSPENSION, $reason, $notes, $performedBy);

        $this->notify($account, new AccountSuspended($reason));

        return $action;
    }

    /**
     * A suspension that also ends the subscription now and refunds the unused part of
     * the last payment. The outcome is appended to the action's notes so the history
     * shows what was refunded.
     *
     * @return array{action: ApiAccountAction, cancelled: bool, refunded_cents: ?int, refund_failed: bool}
     */
    public function suspendWithRefund(
        ApiAccount $account,
        string $reason,
        ?string $notes = null,
        ?int $performedBy = null,
    ): array {
        $action = $this->withdraw($account, ApiAccount::SUSPENSION, $reason, $notes, $performedBy);

        $outcome = $this->cancelAndRefund($account);

        $summary = match (true) {
            ! $outcome['cancelled'] => $outcome['refund_failed']
                ? 'Cancel and refund failed in Stripe. Subscription still running.'
                : 'No subscription to cancel.',
            $outcome['refund_failed'] => 'Subscription cancelled. Refund failed in Stripe — refund by hand.',
            $outcome['refunded_cents'] > 0 => 'Subscription cancelled. Refunded '.Cashier::formatAmount($outcome['refunded_cents']).'.',
            default => 'Subscription cancelled. Nothing to refund.',
        };

        $action->update(['notes' => trim(($notes ?? '')."\n\n".$summary)]);

        $this->notify($account, new AccountSuspended(
            $reason,
            subscriptionCancelled: $outcome['cancelled'],
            refundedCents: $outcome['refund_failed'] ? null : $outcome['refunded_cents'],
        ));

        return ['action' => $action] + $outcome;
    }

    public function terminate(
        ApiAccount $account,
        string $reason,
        ?string $notes = null,
        ?int $performedBy = null,
    ): ApiAccountAction {
        $action = $this->withdraw($account, ApiAccount::TERMINATION, $reason, $notes, $performedBy);

        $cancelled = $this->cancelSubscription($account);

        $this->notify($account, new AccountTerminated($reason, $cancelled));

        return $action;
    }

    /**
     * Restores access. Works on a termination as well as a suspension — terminations
     * are permanent by policy, not by mechanism, and we do make mistakes.
     *
     * What it cannot undo is the cancelled subscription: Stripe has ended it, and
     * reviving it needs the customer's own payment details. A reinstated termination
     * has access to whatever a free account has until they subscribe again, so the
     * console says so before you press it.
     */
    public function reinstate(
        ApiAccount $account,
        ?string $notes = null,
        ?int $performedBy = null,
    ): ApiAccountAction {
        $wasTerminated = $account->isTerminated();

        $action = DB::connection('heroesprofile_api')->transaction(function () use ($account, $notes, $performedBy) {
            $account->forceFill([
                'suspended_at' => null,
                'suspension_type' => null,
                'suspension_reason' => null,
                'suspended_by' => null,
            ])->save();

            return ApiAccountAction::create([
                'api_account_id' => $account->id,
                'action' => ApiAccountAction::REINSTATE,
                'notes' => $notes,
                'performed_by' => $performedBy,
            ]);
        });

        $this->keys->forgetAccount($account->id);

        $this->notify($account, new AccountReinstated($wasTerminated));

        return $action;
    }

    /** Asking what they are doing before deciding whether anything needs fixing. */
    public function info(
        ApiAccount $account,
        string $message,
        ?string $notes = null,
        ?Carbon $respondBy = null,
        ?int $performedBy = null,
    ): ApiAccountAction {
        $action = ApiAccountAction::create([
            'api_account_id' => $account->id,
            'action' => ApiAccountAction::INFO,
            'reason' => $message,
            'notes' => $notes,
            'respond_by' => $respondBy,
            'performed_by' => $performedBy,
        ]);

        $this->notify($account, new AccountInfoRequested($message, $respondBy));

        return $action;
    }

    /** Evidence gathered before deciding whether to act. The customer never sees it. */
    public function note(ApiAccount $account, string $notes, ?int $performedBy = null): ApiAccountAction
    {
        return ApiAccountAction::create([
            'api_account_id' => $account->id,
            'action' => ApiAccountAction::NOTE,
            'notes' => $notes,
            'performed_by' => $performedBy,
        ]);
    }

    /** Dismissing the banner. The timestamp is what proves the warning landed. */
    public function acknowledgeWarning(ApiAccount $account): ?ApiAccountAction
    {
        $warning = $account->unacknowledgedWarning();

        $warning?->acknowledge();

        return $warning;
    }

    /**
     * Sets the account's standing and records why, in one transaction so an account
     * can never end up suspended with no row saying who did it.
     */
    private function withdraw(
        ApiAccount $account,
        string $type,
        string $reason,
        ?string $notes,
        ?int $performedBy,
    ): ApiAccountAction {
        $action = DB::connection('heroesprofile_api')->transaction(function () use ($account, $type, $reason, $notes, $performedBy) {
            $account->forceFill([
                'suspended_at' => now(),
                'suspension_type' => $type,
                'suspension_reason' => $reason,
                'suspended_by' => $performedBy,
            ])->save();

            return ApiAccountAction::create([
                'api_account_id' => $account->id,
                'action' => $type === ApiAccount::TERMINATION
                    ? ApiAccountAction::TERMINATE
                    : ApiAccountAction::SUSPEND,
                'reason' => $reason,
                'notes' => $notes,
                'performed_by' => $performedBy,
            ]);
        });

        // Entitlement is cached with the key for five minutes, so without this they
        // keep calling successfully long after the button was pressed.
        $this->keys->forgetAccount($account->id);

        return $action;
    }

    /**
     * Ends the subscription now rather than at period end. Failure is reported and
     * swallowed: Stripe being unreachable must not leave an account terminated in our
     * records and still working, and the charge can be stopped by hand afterwards.
     */
    private function cancelSubscription(ApiAccount $account): bool
    {
        $subscription = $account->subscription(ApiAccount::SUBSCRIPTION);

        if ($subscription === null || $subscription->canceled()) {
            return false;
        }

        try {
            $subscription->cancelNow();

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Ends the subscription now and refunds the last payment pro rata for the time left
     * in the period. No proration on the cancel itself: that would leave a Stripe credit
     * on top of the refund, applied if they ever subscribe again.
     *
     * A cancel that fails stops here with nothing done. A refund that fails after the
     * cancel went through is reported so it can be issued by hand.
     *
     * @return array{cancelled: bool, refunded_cents: ?int, refund_failed: bool}
     */
    private function cancelAndRefund(ApiAccount $account): array
    {
        $subscription = $account->subscription(ApiAccount::SUBSCRIPTION);

        if ($subscription === null || $subscription->ended()) {
            return ['cancelled' => false, 'refunded_cents' => null, 'refund_failed' => false];
        }

        try {
            // Read before cancelling, while the period is still the live one.
            $periodStart = $subscription->currentPeriodStart();
            $periodEnd = $subscription->currentPeriodEnd();
            $invoice = $subscription->asStripeSubscription(['latest_invoice.payments'])->latest_invoice;

            $subscription->noProrate()->cancelNow();
        } catch (Throwable $e) {
            report($e);

            return ['cancelled' => false, 'refunded_cents' => null, 'refund_failed' => true];
        }

        $paid = (int) ($invoice?->amount_paid ?? 0);
        $intent = collect($invoice?->payments?->data ?? [])
            ->first(fn ($payment) => $payment->status === 'paid')
            ?->payment?->payment_intent;

        if ($paid <= 0 || $intent === null || $periodStart === null || $periodEnd === null) {
            return ['cancelled' => true, 'refunded_cents' => 0, 'refund_failed' => false];
        }

        $length = $periodEnd->getTimestamp() - $periodStart->getTimestamp();
        $left = min($length, max(0, $periodEnd->getTimestamp() - now()->getTimestamp()));
        $amount = $length > 0 ? intdiv($paid * $left, $length) : 0;

        if ($amount <= 0) {
            return ['cancelled' => true, 'refunded_cents' => 0, 'refund_failed' => false];
        }

        try {
            $account->refund(is_string($intent) ? $intent : $intent->id, ['amount' => $amount]);
        } catch (Throwable $e) {
            report($e);

            return ['cancelled' => true, 'refunded_cents' => $amount, 'refund_failed' => true];
        }

        return ['cancelled' => true, 'refunded_cents' => $amount, 'refund_failed' => false];
    }

    /**
     * StandingMail BCCs the admin address. A failing customer address must not throw
     * back into the console after the action has already taken effect.
     */
    private function notify(ApiAccount $account, $notification): void
    {
        try {
            $account->notify($notification);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
