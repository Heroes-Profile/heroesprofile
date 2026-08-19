<?php

namespace App\Services\Api;

use App\Auth\ApiKeyContext;
use App\Models\Api\ApiAccount;
use App\Models\Api\ApiKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApiKeyResolver
{
    private const CACHE_SECONDS = 300;

    private const CONNECTION = 'heroesprofile_api';

    public function resolve(string $plainKey): ?ApiKeyContext
    {
        if ($plainKey === '') {
            return null;
        }

        $hash = ApiKey::hash($plainKey);
        $row = Cache::remember(
            'api_key:'.$hash,
            self::CACHE_SECONDS,
            fn () => $this->lookup($hash) ?? false
        );

        if ($row === false) {
            return null;
        }

        $account = ApiAccount::find($row['account_id']);

        if (! $account) {
            return null;
        }

        return new ApiKeyContext(
            account: $account,
            keyId: (int) $row['key_id'],
            planId: $row['plan_id'] !== null ? (int) $row['plan_id'] : null,
            planName: $row['plan'],
            subscriptionActive: $row['subscription_active'],
            comped: $row['comped'],
        );
    }

    public function forget(string $plainKey): void
    {
        Cache::forget('api_key:'.ApiKey::hash($plainKey));
    }

    /**
     * Subscription still comes from the old `subscriptions` table, which is the live
     * source for both sites until billing moves to Cashier.
     */
    private function lookup(string $hash): ?array
    {
        $approvals = ApiAccount::APPROVAL_COLUMNS;

        $row = DB::connection(self::CONNECTION)
            ->table('api_keys')
            ->join('users', 'users.id', '=', 'api_keys.api_account_id')
            ->leftJoin('subscriptions', 'subscriptions.user_id', '=', 'users.id')
            ->leftJoin('subscription_plans', 'subscription_plans.stripe_plan', '=', 'subscriptions.stripe_plan')
            ->where('api_keys.secret_hash', $hash)
            ->whereNull('api_keys.revoked_at')
            ->orderByDesc('subscriptions.created_at')
            ->select(array_merge([
                'api_keys.id as key_id',
                'users.id as account_id',
                'subscriptions.stripe_status',
                'subscriptions.ends_at',
                'subscription_plans.plan_id',
                'subscription_plans.plan',
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

        return [
            'key_id' => $row->key_id,
            'account_id' => $row->account_id,
            'plan_id' => $row->plan_id,
            'plan' => $row->plan,
            'subscription_active' => $row->stripe_status === 'active' && ! $lapsed,
            'comped' => $comped,
        ];
    }
}
