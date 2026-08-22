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
    ) {}

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
