<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiEndpoint;
use App\Support\ApiSpecConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
        $rateNotes = $this->rateLimitNotes();

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
                'rate_note' => $rateNotes[$endpoint->endpoint] ?? null,
            ];
        }

        usort($groups, fn ($a, $b) => $a['group_sort'] <=> $b['group_sort']);

        return array_values($groups);
    }

    /**
     * The endpoints whose per-minute limit is not simply the plan's, and why.
     *
     * Weekly allowances are the registry's business and are shown per plan above.
     * The per-minute limit is not in the registry at all — it is decided per route
     * in `config/api.php` — so without this the only place it appears to a caller
     * is one row on the pricing page saying 60, which is wrong for these.
     *
     * Read from the route table rather than a hand-kept list: the rate limits are
     * keyed by route name and the registry by endpoint key, and the routes are what
     * carry both.
     *
     * @return array<string, string> endpoint key => note
     */
    private function rateLimitNotes(): array
    {
        $limits = config('api.rate_limits');
        $notes = [];

        foreach (Route::getRoutes() as $route) {
            $endpoint = $this->middlewareArgument($route, 'api.quota');
            $name = $route->getName();

            if ($endpoint === null || $name === null) {
                continue;
            }

            if (in_array($name, $limits['batch_routes'] ?? [], true)) {
                $notes[$endpoint] = $limits['batch'].' a minute — one call here runs a query per hero.';

                continue;
            }

            if (isset($limits['routes'][$name])) {
                $notes[$endpoint] = $limits['routes'][$name].' a minute, on every plan.';

                continue;
            }

            if (ApiSpecConfig::declaresParameter($name, 'group_by_map')) {
                $notes[$endpoint] = 'Drops to '.$limits['batch'].' a minute with `group_by_map=true`, which runs a query per map.';
            }
        }

        return $notes;
    }

    /** The endpoint key a route is metered under, from its `api.quota:` middleware. */
    private function middlewareArgument($route, string $alias): ?string
    {
        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, $alias.':')) {
                return substr($middleware, strlen($alias) + 1);
            }
        }

        return null;
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
