<?php

namespace App\Services\Api;

use App\Models\Api\ApiAccount;
use App\Models\Api\ApiAccountAction;
use App\Notifications\Api\AccountReinstated;
use App\Notifications\Api\AccountSuspended;
use App\Notifications\Api\AccountTerminated;
use App\Notifications\Api\AccountWarned;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
 *   something much larger than intended.
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
     * No admin copy, unlike the subscription mails: an admin pressed the button and
     * the action row is the record. A failing customer address must not throw back
     * into the console after the action has already taken effect.
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
