<?php

namespace App\Services\Api;

use App\Models\Api\ApiAccount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Throwable;

class PlanService
{
    private const PRICE_CACHE_SECONDS = 86400;

    /** @return array<int, array<string, mixed>> keyed by plan id */
    public function all(): array
    {
        return config('api_plans.plans');
    }

    /** @return array<int, array<string, mixed>> */
    public function paid(): array
    {
        return array_filter($this->all(), fn (array $plan) => $plan['paid']);
    }

    /**
     * Plans this account may buy. Everyone sees the self-serve tiers except
     * Developer, which needs `d_approved`.
     *
     * Comped tiers are never purchasable — they are granted by flag. See
     * grantedTo().
     */
    public function selectableBy(ApiAccount $account): array
    {
        return array_filter($this->all(), function (array $plan, int $planId) use ($account) {
            if (! $plan['paid']) {
                return false;
            }

            return $planId === 3 ? (bool) $account->d_approved : true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Comped tiers this account has been granted. Shown as special access rather
     * than something to buy, and held alongside any purchased plan.
     */
    public function grantedTo(ApiAccount $account): array
    {
        $granted = [];

        foreach (config('api_plans.comped_flags') as $flag => $planId) {
            if ((bool) $account->{$flag}) {
                $granted[$planId] = $this->all()[$planId];
            }
        }

        return $granted;
    }

    /**
     * Monthly price in whole currency units.
     *
     * Stripe holds the real amount; the config value is only used when the lookup
     * fails, so an outage shows a possibly-stale figure rather than nothing.
     */
    public function priceFor(int $planId): ?int
    {
        $plan = $this->all()[$planId] ?? null;

        if ($plan === null) {
            return null;
        }

        if (! $plan['paid']) {
            return 0;
        }

        $fromStripe = Cache::remember(
            'stripe_price_amount:'.$plan['stripe_price'],
            self::PRICE_CACHE_SECONDS,
            function () use ($plan) {
                try {
                    $price = Cashier::stripe()->prices->retrieve($plan['stripe_price']);

                    return (int) round($price->unit_amount / 100);
                } catch (Throwable $e) {
                    Log::warning('Stripe price lookup failed', [
                        'stripe_price' => $plan['stripe_price'],
                        'error' => $e->getMessage(),
                    ]);

                    return false;
                }
            }
        );

        return $fromStripe === false ? $plan['price'] : $fromStripe;
    }

    /** The same plan list the landing page and billing page render. */
    public function present(array $plans): array
    {
        $presented = [];

        foreach ($plans as $planId => $plan) {
            $presented[] = $plan + [
                'id' => $planId,
                'price' => $this->priceFor($planId),
            ];
        }

        return $presented;
    }
}
