<?php

namespace App\Services\Twitch;

use App\Models\Api\ApiAccount;
use App\Models\Api\TwitchChannel;
use App\Services\Api\UsageService;
use Illuminate\Support\Facades\Cache;

/**
 * Any API plan the account holds — bought, comped, or earned by a Patreon pledge —
 * includes the extension. There is no Twitch plan of its own.
 *
 * The plan comes from UsageService::planIdsFor(), the same answer the portal's
 * usage table shows, so the extension and the API cannot disagree about whether an
 * account is paying.
 */
class TwitchEntitlementService
{
    private const CACHE_SECONDS = 300;

    public function __construct(private readonly UsageService $usage) {}

    public function for(TwitchChannel $channel): TwitchEntitlement
    {
        $account = $channel->user_id !== null ? $channel->account : null;

        return TwitchEntitlement::decide(
            accountLinked: $account !== null,
            accountSuspended: $account?->isSuspended() ?? false,
            planName: $account !== null ? $this->planName($account) : null,
            channelSuspendedAt: $channel->suspended_at,
            compedUntil: $channel->comped_until,
            trialStartedAt: $channel->trial_started_at,
            trialEndsAt: $channel->trial_ends_at,
            now: now(),
        );
    }

    /**
     * Called from ApiKeyResolver::forgetAccount(), which every plan change —
     * billing, webhooks, Patreon, admin flags — already goes through. Static so the
     * resolver does not need this service injected, which would be circular.
     */
    public static function forgetAccount(int $accountId): void
    {
        Cache::forget(self::cacheKey($accountId));
    }

    /**
     * Cached, because the snapshot ingest asks on every post. Stored as a string
     * rather than the entitlement itself: the trial and comp dates live on the
     * channel row, which is already loaded, and must not lag.
     */
    private function planName(ApiAccount $account): ?string
    {
        $name = Cache::remember(self::cacheKey($account->id), self::CACHE_SECONDS, function () use ($account) {
            if ($account->isAdmin()) {
                return 'Admin';
            }

            $planIds = $this->usage->planIdsFor($account);

            if ($planIds === []) {
                return '';
            }

            return (string) (config("api_plans.plans.{$planIds[0]}.name") ?? 'API plan');
        });

        return $name === '' ? null : $name;
    }

    private static function cacheKey(int $accountId): string
    {
        return 'twitch_plan:'.$accountId;
    }
}
