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
        $plans = array_filter($this->all(), fn (array $plan) => $plan['paid']);

        // A tier already granted must not also be offered for sale. Only Patreon can
        // put a *paid* tier in here — the comped flags all point at free ones — so
        // without this a $10 supporter is invited to buy Intermediate twice.
        $granted = $this->grantedTo($account);

        foreach ($plans as $planId => $plan) {
            // Developer is shown to everyone but only bought after approval.
            $plans[$planId]['purchasable'] = $planId === 3
                ? (bool) $account->d_approved
                : true;

            if (array_key_exists($planId, $granted)) {
                $plans[$planId]['purchasable'] = false;
            }
        }

        return $plans;
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

        // Patreon grants a tier that is also sold, unlike every flag above. It still
        // belongs here: this is what the billing page lists as already held, and
        // selectableBy() reads the same answer to stop offering it for sale.
        $patreonPlanId = $this->planIdForPatreonCents($account->patreonPledgeCents());

        if ($patreonPlanId !== null) {
            $granted[$patreonPlanId] = $this->all()[$patreonPlanId];
        }

        return $granted;
    }

    /**
     * Plan earned by a pledge, or null.
     *
     * Thresholds are read highest first, so $10 stops at Intermediate rather than
     * falling through to Basic. Lives here rather than in ApiKeyResolver so the
     * billing page and the key guard cannot disagree about what a pledge buys.
     */
    public function planIdForPatreonCents(mixed $cents): ?int
    {
        if (! is_numeric($cents)) {
            return null;
        }

        $tiers = config('api_plans.patreon_tiers', []);

        krsort($tiers);

        foreach ($tiers as $threshold => $planId) {
            if ((int) $cents >= $threshold) {
                return $planId;
            }
        }

        return null;
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

    /** The plan a Stripe Price belongs to, or null when it is not one of ours. */
    public function planIdForPrice(?string $stripePrice): ?int
    {
        if ($stripePrice === null) {
            return null;
        }

        foreach ($this->all() as $planId => $plan) {
            if ($plan['stripe_price'] === $stripePrice) {
                return $planId;
            }
        }

        return null;
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
