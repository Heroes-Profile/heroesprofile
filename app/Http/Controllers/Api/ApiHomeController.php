<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiEndpoint;
use Illuminate\Support\Facades\Auth;

class ApiHomeController extends Controller
{
    public function index()
    {
        return view('api.index', [
            'authenticated' => Auth::guard('api_web')->check(),
            'plans' => array_values($this->paidPlans()),
            'pricingData' => $this->pricingSummary(),
        ]);
    }

    /**
     * Developer tier is not self-serve — it needs `d_approved` on the account —
     * so the CTA sends people here to ask for it.
     */
    public function developerTier()
    {
        return view('api.developer-tier');
    }

    /**
     * Every endpoint and what each tier allows, rather than the landing page's
     * one-row-per-group summary.
     */
    public function endpointLimits()
    {
        return view('api.endpoint-limits', [
            'plans' => array_values($this->paidPlans()),
            'groups' => $this->endpointLimitDetail(),
        ]);
    }

    /**
     * One row per endpoint, carrying every paid tier's weekly allowance.
     *
     * @return array<int, array<string, mixed>>
     */
    private function endpointLimitDetail(): array
    {
        $plans = $this->paidPlans();

        $endpoints = ApiEndpoint::query()
            ->with(['quotas' => fn ($query) => $query->whereIn('subscription_plan', array_keys($plans))])
            ->excludingEsports()
            ->ordered()
            ->get();

        $groups = [];

        foreach ($endpoints as $endpoint) {
            // A registry row with no endpoint key drives nothing and cannot be
            // called, so it has no allowance to show.
            if (($endpoint->endpoint ?? '') === '') {
                continue;
            }

            $group = $endpoint->group_name;

            if (! isset($groups[$group])) {
                $groups[$group] = [
                    'title' => $group,
                    'group_sort' => $endpoint->group_sort,
                    'endpoints' => [],
                ];
            }

            $limits = [];

            foreach ($plans as $planId => $plan) {
                $quota = $endpoint->quotas->firstWhere('subscription_plan', $planId);
                $limits[$plan['key']] = $this->formatCallLimit($quota?->calls_per_week);
            }

            $groups[$group]['endpoints'][] = [
                'name' => $endpoint->name,
                'endpoint' => $endpoint->endpoint,
                'limits' => $limits,
            ];
        }

        usort($groups, fn ($a, $b) => $a['group_sort'] <=> $b['group_sort']);

        return array_values($groups);
    }

    /** @return array<int, array<string, mixed>> keyed by subscription_plan id */
    private function paidPlans(): array
    {
        return array_filter(
            config('api_plans.plans'),
            fn (array $plan) => $plan['paid']
        );
    }

    /**
     * One row per endpoint group, showing each tier's weekly allowance.
     */
    private function pricingSummary(): array
    {
        $plans = $this->paidPlans();

        $endpoints = ApiEndpoint::query()
            ->with(['quotas' => fn ($query) => $query->whereIn('subscription_plan', array_keys($plans))])
            ->excludingEsports()
            ->ordered()
            ->get();

        $groups = [];

        foreach ($endpoints as $endpoint) {
            $group = $endpoint->group_name;

            if (! isset($groups[$group])) {
                $groups[$group] = [
                    'title' => $group,
                    'group_sort' => $endpoint->group_sort,
                    'basic' => null,
                    'intermediate' => null,
                    'developer' => null,
                ];
            }

            // First endpoint in a group stands for the whole group.
            foreach ($endpoint->quotas as $quota) {
                $key = $plans[$quota->subscription_plan]['key'];

                if ($groups[$group][$key] === null) {
                    $groups[$group][$key] = $this->formatCallLimit($quota->calls_per_week);
                }
            }
        }

        usort($groups, fn ($a, $b) => $a['group_sort'] <=> $b['group_sort']);

        return array_values($groups);
    }

    /**
     * A zero or absent allowance is not free access — `EnforceApiQuota` answers it
     * with a 403 saying the plan does not include the endpoint. Saying "Free" here
     * advertised access that the API refuses.
     */
    private function formatCallLimit(?int $calls): string
    {
        if ($calls === null || $calls === 0) {
            return 'Not included';
        }

        return number_format($calls);
    }
}
