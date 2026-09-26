<?php

namespace App\Services\Twitch;

use Carbon\CarbonInterface;

/**
 * Whether a channel may use the extension right now, and why.
 *
 * `pending` is a channel that has never sent live data: the trial has not started,
 * and it starts on the first accepted snapshot, so it counts as active.
 */
final class TwitchEntitlement
{
    public const INACTIVE = 'inactive';

    public const SUSPENDED = 'suspended';

    public const PAID = 'paid';

    public const COMP = 'comp';

    public const TRIAL = 'trial';

    public const PENDING = 'pending';

    public function __construct(
        public readonly string $source,
        public readonly ?string $planName = null,
        public readonly ?CarbonInterface $expiresAt = null,
    ) {}

    public function isActive(): bool
    {
        return in_array($this->source, [self::PAID, self::COMP, self::TRIAL, self::PENDING], true);
    }

    /**
     * The decision, with no I/O, so every branch can be unit tested.
     *
     * Paid access comes first: a streamer on a plan should never be told their
     * trial is running out.
     *
     * @param  bool  $accountLinked  the channel belongs to an API account
     * @param  bool  $accountSuspended  that account is suspended or terminated
     * @param  string|null  $planName  the API plan the account holds, if any
     */
    public static function decide(
        bool $accountLinked,
        bool $accountSuspended,
        ?string $planName,
        ?CarbonInterface $channelSuspendedAt,
        ?CarbonInterface $compedUntil,
        ?CarbonInterface $trialStartedAt,
        ?CarbonInterface $trialEndsAt,
        CarbonInterface $now,
    ): self {
        if ($channelSuspendedAt !== null || $accountSuspended) {
            return new self(self::SUSPENDED);
        }

        if (! $accountLinked) {
            return new self(self::INACTIVE);
        }

        if ($planName !== null) {
            return new self(self::PAID, $planName);
        }

        if ($compedUntil !== null && $compedUntil->greaterThan($now)) {
            return new self(self::COMP, null, $compedUntil);
        }

        if ($trialStartedAt === null) {
            return new self(self::PENDING);
        }

        if ($trialEndsAt !== null && $trialEndsAt->greaterThan($now)) {
            return new self(self::TRIAL, null, $trialEndsAt);
        }

        return new self(self::INACTIVE);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'active' => $this->isActive(),
            'source' => $this->source,
            'plan' => $this->planName,
            'expires_at' => $this->expiresAt?->toIso8601String(),
        ];
    }
}
