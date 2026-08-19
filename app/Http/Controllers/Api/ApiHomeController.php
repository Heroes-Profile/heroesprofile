<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiEndpoint;
use Illuminate\Support\Facades\Auth;

class ApiHomeController extends Controller
{
    /** Paid tiers shown on the landing page, keyed by subscription_plan id. */
    private const PLANS = [
        1 => ['key' => 'basic', 'name' => 'Basic', 'price' => 5],
        2 => ['key' => 'intermediate', 'name' => 'Intermediate', 'price' => 10],
        3 => ['key' => 'developer', 'name' => 'Developer', 'price' => 25],
    ];

    public function index()
    {
        return view('api.index', [
            'authenticated' => Auth::guard('api_web')->check(),
            'plans' => array_values(self::PLANS),
            'pricingData' => $this->pricingSummary(),
        ]);
    }

    /**
     * One row per endpoint group, showing each tier's weekly allowance.
     */
    private function pricingSummary(): array
    {
        $endpoints = ApiEndpoint::query()
            ->with(['quotas' => fn ($query) => $query->whereIn('subscription_plan', array_keys(self::PLANS))])
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
                $key = self::PLANS[$quota->subscription_plan]['key'];

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
