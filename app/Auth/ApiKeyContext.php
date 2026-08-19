<?php

namespace App\Auth;

use App\Models\Api\ApiAccount;

/**
 * What the guard resolved from a key. Carries entitlement but decides nothing —
 * EnforceApiQuota reads this and does the rejecting.
 */
class ApiKeyContext
{
    public function __construct(
        public readonly ApiAccount $account,
        public readonly int $keyId,
        public readonly ?int $planId,
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
}
