<?php

namespace App\Auth;

use App\Models\Api\ApiAccount;

/**
 * What the guard resolved from a key. Carries entitlement but decides nothing —
 * EnforceApiQuota reads this and does the rejecting.
 */
class ApiKeyContext
{
    /**
     * @param  array<int, int>  $planIds  Every plan this account holds. A comped
     *                                    account that also buys a tier holds both,
     *                                    and each endpoint uses the highest
     *                                    allowance among them.
     * @param  bool  $subscriptionUnresolved  A subscription row exists but named no
     *                                        plan we recognise. Never the same thing
     *                                        as holding no subscription.
     */
    public function __construct(
        public readonly ApiAccount $account,
        public readonly int $keyId,
        public readonly array $planIds,
        public readonly ?string $planName,
        public readonly bool $subscriptionActive,
        public readonly bool $comped,
        public readonly bool $subscriptionUnresolved = false,
        public readonly ?string $unresolvedReason = null,
        public readonly bool $suspended = false,
        public readonly ?string $suspensionType = null,
        public readonly ?string $suspensionReason = null,
    ) {}

    /**
     * Access withdrawn for conduct. Nothing to do with billing, and answered before
     * entitlement is even looked at.
     */
    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    public function isTerminated(): bool
    {
        return $this->suspended && $this->suspensionType === ApiAccount::TERMINATION;
    }

    /**
     * What the caller is told, in the same words as the email and the portal banner.
     *
     * The reason is always included. A refusal that will not say why leaves the
     * integrator to guess, and they guess "outage" and retry forever.
     */
    public function suspensionMessage(): ?string
    {
        if (! $this->suspended) {
            return null;
        }

        $reason = trim((string) $this->suspensionReason);

        $opening = $this->isTerminated()
            ? 'This account has been closed for breaching the API terms.'
            : 'This account is suspended for breaching the API terms.';

        return trim($opening.' '.$reason).' Sign in at heroesprofile.com/Api/Account or write to zemill@heroesprofile.com.';
    }

    /**
     * Why entitlement could not be read, in words a customer can act on.
     *
     * `retired_plan` — they are on a tier we no longer sell.
     * `not_backfilled` — their subscription has not been copied to the new billing
     * tables yet. Transitional; goes away with the old site.
     */
    public function unresolvedMessage(): ?string
    {
        if (! $this->subscriptionUnresolved) {
            return null;
        }

        return match ($this->unresolvedReason) {
            'retired_plan' => 'Your subscription is on a plan we no longer offer, so we cannot tell what it includes. Pick a current plan on the billing page, or contact us and we will sort it out.',
            'not_backfilled' => 'Your subscription has not finished moving to our new billing system. This is our doing, not yours — contact us and we will fix it.',
            // Entries cached before the reason existed say only that something is
            // wrong. Better a vague message than an empty one.
            default => 'Your subscription could not be matched to a plan. Contact us and we will sort it out.',
        };
    }

    public function isEntitled(): bool
    {
        return $this->subscriptionActive || $this->comped;
    }

    public function hasMigrated(): bool
    {
        return $this->account->hasMigrated();
    }

    /** Serve fixtures and charge no quota. */
    public function receivesTestData(): bool
    {
        return $this->account->receivesTestData();
    }

    /**
     * An account holding no plan at all gets fixtures rather than a rejection, so a
     * new signup can integrate before paying. A plan that exists but has lapsed is a
     * different case and still fails entitlement.
     */
    public function servesFixtures(): bool
    {
        // An admin exercising their grant is judged on test mode alone. The
        // no-plan fallback below would otherwise pin them to fixtures whatever
        // they set, since an admin has no reason to hold a subscription.
        if ($this->account->actingAsAdmin()) {
            return $this->account->inTestMode();
        }

        // Asked for explicitly, or still behind the migration gate.
        if ($this->receivesTestData()) {
            return true;
        }

        // Holding an unreadable subscription is not the same as holding none. The
        // fallback below is for people who have not bought anything; someone whose
        // payment we cannot map to a plan has to fail loudly instead.
        if ($this->subscriptionUnresolved) {
            return false;
        }

        return $this->planIds === [];
    }
}
