<?php

namespace App\Services\Api;

use App\Auth\ApiKeyContext;
use App\Models\Api\ApiAccount;
use App\Models\Api\ApiKey;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApiKeyResolver
{
    private const CACHE_SECONDS = 300;

    private const CONNECTION = 'heroesprofile_api';

    public function __construct(private readonly PlanService $plans) {}

    public function resolve(string $plainKey): ?ApiKeyContext
    {
        if ($plainKey === '') {
            return null;
        }

        $hash = ApiKey::hash($plainKey);
        $row = Cache::remember(
            'api_key:'.$hash,
            self::CACHE_SECONDS,
            fn () => $this->lookup(fn ($query) => $query->where('api_keys.secret_hash', $hash)) ?? false
        );

        if ($row === false) {
            return null;
        }

        $account = ApiAccount::find($row['account_id']);

        if (! $account) {
            return null;
        }

        $this->touchLastUsed((int) $row['key_id']);

        return new ApiKeyContext(
            account: $account,
            keyId: (int) $row['key_id'],
            planIds: $row['plan_ids'],
            planName: $row['plan'],
            subscriptionActive: $row['subscription_active'],
            comped: $row['comped'],
            // Coalesced: entries cached before these fields existed outlive a deploy.
            subscriptionUnresolved: $row['subscription_unresolved'] ?? false,
            unresolvedReason: $row['unresolved_reason'] ?? null,
        );
    }

    /**
     * The same context, resolved from an account rather than from a key.
     *
     * The portal's test client executes calls for a signed-in account, and keys
     * are hashed — so there is no plaintext to resolve from. Entitlement still
     * comes from one of the account's own keys, and quota is charged to it, so a
     * call made here counts exactly as the caller's own would.
     *
     * Deliberately uncached: revocation clears the hash-keyed entries, and a
     * second cache namespace it did not know about would outlive a revoked key.
     */
    public function resolveForAccount(int $accountId): ?ApiKeyContext
    {
        $row = $this->lookup(fn ($query) => $query->where('api_keys.api_account_id', $accountId));

        if ($row === null) {
            return null;
        }

        $account = ApiAccount::find($row['account_id']);

        if (! $account) {
            return null;
        }

        return new ApiKeyContext(
            account: $account,
            keyId: (int) $row['key_id'],
            planIds: $row['plan_ids'],
            planName: $row['plan'],
            subscriptionActive: $row['subscription_active'],
            comped: $row['comped'],
            // Coalesced: entries cached before these fields existed outlive a deploy.
            subscriptionUnresolved: $row['subscription_unresolved'] ?? false,
            unresolvedReason: $row['unresolved_reason'] ?? null,
        );
    }

    public function forget(string $plainKey): void
    {
        $this->forgetHash(ApiKey::hash($plainKey));
    }

    /** Used on revoke, where only the stored hash is available. */
    public function forgetHash(string $hash): void
    {
        Cache::forget('api_key:'.$hash);
    }

    /**
     * Drops every cached entry for an account, across all of its keys.
     *
     * Entitlement is cached with the key, so a billing change is invisible until the
     * entry ages out — five minutes of a cancelled account still working, or of
     * someone who just upgraded being told their plan does not cover an endpoint.
     * Call this whenever a subscription changes, wherever the change came from.
     */
    public function forgetAccount(int $accountId): void
    {
        ApiKey::where('api_account_id', $accountId)
            ->pluck('secret_hash')
            ->each(fn (string $hash) => $this->forgetHash($hash));
    }

    /**
     * Every comped grant on the account, not just the first — an account flagged
     * for both Partner and NGS holds both.
     *
     * @return array<int, int>
     */
    private function plansFromApprovalFlags(object $row): array
    {
        $planIds = [];

        foreach (config('api_plans.comped_flags') as $column => $planId) {
            if ((bool) ($row->{$column} ?? false)) {
                $planIds[] = $planId;
            }
        }

        return $planIds;
    }

    /** Throttled to one write per key per 5 minutes rather than one per request. */
    private function touchLastUsed(int $keyId): void
    {
        $marker = 'api_key_touched:'.$keyId;

        if (Cache::has($marker)) {
            return;
        }

        Cache::put($marker, true, self::CACHE_SECONDS);

        ApiKey::where('id', $keyId)->update(['last_used_at' => now()]);
    }

    /**
     * Subscription comes from `cashier_subscriptions`, the same table the billing
     * page reads. The old Spark `subscriptions` table is no longer consulted here:
     * it stops being written the moment Spark's billing UI is disabled, and reading
     * one table while writing another is how a paying customer ends up on fixtures.
     *
     * Cancellations still happen on the old site during the transition. Those reach
     * this table through Stripe's webhook, not through Spark — so the webhook is a
     * prerequisite, not a convenience.
     *
     * @param  Closure(Builder): void  $constrain  which key to find
     */
    private function lookup(Closure $constrain): ?array
    {
        $approvals = ApiAccount::APPROVAL_COLUMNS;

        $row = DB::connection(self::CONNECTION)
            ->table('api_keys')
            ->join('users', 'users.id', '=', 'api_keys.api_account_id')
            // Type pinned in the join rather than a where, which would drop every
            // account that holds no subscription at all.
            ->leftJoin('cashier_subscriptions', function ($join) {
                $join->on('cashier_subscriptions.user_id', '=', 'users.id')
                    ->where('cashier_subscriptions.type', '=', ApiAccount::SUBSCRIPTION);
            })
            // Read live rather than copied into a flag. The comped columns are static
            // grants with nothing to reconcile them, so a copied pledge would outlive
            // the support that earned it. Resolving here makes a lapsed patron answer
            // itself on the next call. Cross-schema, same server — the old site's
            // ApiTokenValidator joined this way too.
            ->leftJoin($this->patreonTable().' as patreon_accounts', 'patreon_accounts.patreon_accounts_id', '=', 'users.patreon_accounts_id')
            ->whereNull('api_keys.revoked_at')
            ->tap($constrain)
            ->orderByDesc('cashier_subscriptions.created_at')
            ->select(array_merge([
                'api_keys.id as key_id',
                'users.id as account_id',
                // Distinguishes "no subscription at all" from "subscription whose
                // plan did not resolve". `stripe_status` cannot do that job — a
                // handful of legacy rows carry a null status.
                'cashier_subscriptions.id as subscription_id',
                'cashier_subscriptions.stripe_status',
                'cashier_subscriptions.stripe_price',
                'cashier_subscriptions.ends_at',
                'patreon_accounts.currently_entitled_amount_cents as patreon_cents',
            ], array_map(fn ($column) => 'users.'.$column, $approvals)))
            ->first();

        if (! $row) {
            return null;
        }

        $lapsed = $row->ends_at !== null && now()->gte($row->ends_at);

        $comped = false;
        foreach ($approvals as $column) {
            if ((bool) ($row->{$column} ?? false)) {
                $comped = true;
                break;
            }
        }

        // Resolved from config, the same map the billing page and usage table use.
        // Reading the plan from a second place is what let the two disagree.
        $purchasedPlanId = $this->plans->planIdForPrice($row->stripe_price);

        // An account can hold several plans at once: a purchased subscription plus
        // any comped grants. Every one counts, and each endpoint uses whichever
        // gives the highest allowance.
        $planIds = [];
        $planName = null;

        if ($purchasedPlanId !== null) {
            $planIds[] = $purchasedPlanId;
            $planName = config("api_plans.plans.{$purchasedPlanId}.key");
        }

        foreach ($this->plansFromApprovalFlags($row) as $compedPlanId) {
            $planIds[] = $compedPlanId;
            $planName ??= config("api_plans.plans.{$compedPlanId}.key");
        }

        // Stacks with everything above. EnforceApiQuota already takes the most
        // generous allowance per endpoint across the plans held, so a supporter who
        // also pays is never worse off for supporting.
        $patreonPlanId = $this->plans->planIdForPatreonCents($row->patreon_cents ?? null);

        if ($patreonPlanId !== null) {
            $planIds[] = $patreonPlanId;
            $planName ??= config("api_plans.plans.{$patreonPlanId}.key");
        }

        // Neither of these may fall through to the no-plan fixture path. That path
        // exists so someone who has not bought anything can evaluate the API; a payer
        // landing on it gets sample data while their card is charged, and nothing
        // anywhere says so.
        //
        // Asked only when nothing else granted a plan. A comped account carries its
        // own entitlement, and the old site faked those grants with placeholder
        // `subscriptions` rows that the backfill skips on purpose — so every comped
        // account looks exactly like a backfill failure unless this checks first.
        $unresolvedReason = $planIds !== [] ? null : match (true) {
            // A plan we no longer sell. Retired tiers still sit in
            // `subscription_plans` but are deliberately absent from config.
            $row->subscription_id !== null && $purchasedPlanId === null => 'retired_plan',

            // TRANSITIONAL — delete with the old site. Spark writes to
            // `subscriptions` for the whole coexistence window, so a row can exist
            // there and not in `cashier_subscriptions` at any point: before the
            // backfill runs, or after it if one slipped through. Checked only when
            // Cashier has nothing, so it costs a query for genuine free accounts
            // and none for anyone else.
            $row->subscription_id === null && $this->hasLegacySubscription((int) $row->account_id) => 'not_backfilled',

            default => null,
        };

        return [
            'key_id' => $row->key_id,
            'account_id' => $row->account_id,
            'plan_ids' => array_values(array_unique($planIds)),
            'plan' => $planName,
            // Matches Cashier's own `valid()`: trialing counts, and past_due does
            // not, because Cashier deactivates it by default. Diverging here is what
            // would deny a trial the billing page shows as fine.
            'subscription_active' => in_array($row->stripe_status, ['active', 'trialing'], true) && ! $lapsed,
            'comped' => $comped || $patreonPlanId !== null,
            'subscription_unresolved' => $unresolvedReason !== null,
            'unresolved_reason' => $unresolvedReason,
        ];
    }

    /**
     * Schema-qualified `patreon_accounts`, for the cross-schema join.
     *
     * Read from the connection config rather than written literally: the database name
     * comes from env and differs between environments, and a hardcoded `heroesprofile.`
     * would silently join the wrong schema on any box that renames it.
     */
    private function patreonTable(): string
    {
        return config('database.connections.heroesprofile.database').'.patreon_accounts';
    }

    /**
     * TRANSITIONAL — remove when the old API site is decommissioned.
     *
     * Whether Spark holds an **active** subscription for this account. Only ever asked
     * when `cashier_subscriptions` holds none, where the answer separates a customer
     * whose row has not been copied across from someone who genuinely never
     * subscribed. Those two are indistinguishable otherwise, and one of them is paying.
     *
     * Restricted to `active` on purpose. Most legacy rows are dead — expired, cancelled,
     * or hand-made placeholders for comped grants and retired tiers, several carrying a
     * null status and an `ends_at` years out. Those accounts are not entitled to
     * anything and belong on the ordinary no-plan fixture path; refusing them would be
     * a loud error for people who are paying nothing and losing nothing.
     *
     * A null-status row that Stripe still considers live is not missed: the backfill
     * resolves its status from Stripe (`resolveStatus()`), after which the account has
     * a Cashier row and this is never asked.
     */
    private function hasLegacySubscription(int $accountId): bool
    {
        return DB::connection(self::CONNECTION)
            ->table('subscriptions')
            ->where('user_id', $accountId)
            ->where('stripe_status', 'active')
            ->exists();
    }
}
