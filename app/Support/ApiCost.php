<?php

namespace App\Support;

/**
 * What an account's API usage cost us, in dollars.
 *
 * Two halves. Egress is what leaves the network; compute is the Cloud Run instance
 * time the app spent producing it. For most endpoints compute is rounding error, but
 * the replay download streams the file through the container, so the container is
 * held for as long as the client takes to receive it — there, compute is real.
 *
 * Rates and the service's shape come from `config('api.costs')`.
 *
 * Deliberately not a generated column like `api_usage.egress_cost_usd`. That one
 * bakes GCP's price breaks into the table definition, so a price change means a
 * migration; this reads config. It is also why the two disagree by design — the
 * generated column knows nothing about compute.
 *
 * Every figure here is an estimate, and an upper bound at that. See
 * `assumed_concurrency` in the config for why.
 */
class ApiCost
{
    private const GIB = 1024 ** 3;

    public static function egress(int $bytes): float
    {
        if ($bytes <= 0) {
            return 0.0;
        }

        return ($bytes / self::GIB) * (float) config('api.costs.egress_per_gib');
    }

    /**
     * Instance time plus the per-request charge.
     *
     * The seconds are divided by the assumed concurrency because Cloud Run bills the
     * instance rather than the request; the per-request charge is not, because that
     * one really is per request.
     */
    public static function compute(int $computeMs, int $calls = 0): float
    {
        $costs = config('api.costs');

        $perSecond = ((float) $costs['vcpus'] * (float) $costs['cpu_per_vcpu_second'])
            + ((float) $costs['memory_gib'] * (float) $costs['memory_per_gib_second']);

        $concurrency = max(1.0, (float) $costs['assumed_concurrency']);

        $instance = max(0, $computeMs) / 1000 * $perSecond / $concurrency;
        $requests = max(0, $calls) * ((float) $costs['per_million_requests'] / 1_000_000);

        return $instance + $requests;
    }

    public static function total(int $bytes, int $computeMs, int $calls = 0): float
    {
        return self::egress($bytes) + self::compute($computeMs, $calls);
    }
}
