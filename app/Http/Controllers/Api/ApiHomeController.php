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

    private function formatCallLimit(?int $calls): string
    {
        if ($calls === null || $calls === 0) {
            return 'Free';
        }

        return number_format($calls);
    }
}
