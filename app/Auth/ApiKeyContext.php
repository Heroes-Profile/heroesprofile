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
     */
    public function __construct(
        public readonly ApiAccount $account,
        public readonly int $keyId,
        public readonly array $planIds,
        public readonly ?string $planName,
        public readonly bool $subscriptionActive,
        public readonly bool $comped,
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
        return $this->receivesTestData() || $this->planIds === [];
    }
}
