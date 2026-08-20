<?php

namespace App\Services\Api;

use App\Models\Api\ApiAccount;
use App\Models\Api\ApiEndpoint;
use App\Models\Api\ApiUsage;

/**
 * Per-endpoint call usage for an account, reported the same way EnforceApiQuota
 * measures it: highest allowance across the plans held, against a rolling
 * seven-day window per endpoint.
 *
 * Entitlement here comes from Cashier. Enforcement still reads the old Spark
 * `subscriptions` table via ApiKeyResolver until the billing cutover, so the two
 * only agree for accounts that exist in both.
 */
class UsageService
{
    public function __construct(private PlanService $plans) {}

    /**
     * Every registry endpoint, grouped and in registry order. Endpoints the
     * account's plans do not cover are included with a zero limit rather than
     * dropped, so the page shows what a different tier would carry.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forAccount(ApiAccount $account): array
    {
        $planIds = $this->planIdsFor($account);

        $usage = ApiUsage::where('api_account_id', $account->id)
            ->get()
            ->keyBy('endpoint');

        $endpoints = ApiEndpoint::query()
            ->with(['quotas' => fn ($query) => $query->whereIn('subscription_plan', $planIds ?: [0])])
            ->ordered()
            ->get();

        $groups = [];

        foreach ($endpoints as $endpoint) {
            if (($endpoint->endpoint ?? '') === '') {
                continue;
            }

            $group = $endpoint->group_name;

            if (! isset($groups[$group])) {
                $groups[$group] = ['title' => $group, 'endpoints' => []];
            }

            $groups[$group]['endpoints'][] = $this->row($endpoint, $usage->get($endpoint->endpoint));
        }

        return array_values($groups);
    }

    /**
     * Every plan the account holds — a purchased subscription plus any comped
     * grants, the same union ApiKeyResolver builds for enforcement.
     *
     * @return array<int, int>
     */
    public function planIdsFor(ApiAccount $account): array
    {
        $planIds = array_keys($this->plans->grantedTo($account));

        $subscription = $account->subscription(ApiAccount::SUBSCRIPTION);

        if ($subscription !== null && $subscription->valid()) {
            $planId = $this->plans->planIdForPrice($subscription->stripe_price);

            if ($planId !== null) {
                $planIds[] = $planId;
            }
        }

        return array_values(array_unique($planIds));
    }

    /** @return array<string, mixed> */
    private function row(ApiEndpoint $endpoint, ?ApiUsage $usage): array
    {
        $limit = (int) $endpoint->quotas->max('calls_per_week');

        // An aged-out window reads as zero here; the middleware resets the row on
        // the next call rather than on a schedule.
        $counted = $usage !== null && ! $usage->windowHasExpired();
        $used = $counted ? $usage->calls : 0;

        return [
            'endpoint' => $endpoint->endpoint,
            'name' => $endpoint->name,
            'included' => $limit > 0,
            'limit' => $limit,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'resets_at' => $counted
                ? $usage->window_started_at->copy()->addDays(ApiUsage::WINDOW_DAYS)->toDateString()
                : null,
        ];
    }
}
