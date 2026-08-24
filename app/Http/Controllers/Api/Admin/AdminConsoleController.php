<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Account\ApiKeyController;
use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use App\Models\Api\ApiKey;
use App\Models\Api\CashierSubscription;
use App\Services\Api\ApiKeyResolver;
use App\Services\Api\PlanService;
use App\Services\Api\UsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminConsoleController extends Controller
{
    private const SEARCH_LIMIT = 25;

    private const ACTIVITY_LIMIT = 50;

    public function show()
    {
        return view('api.admin.console');
    }

    /**
     * Email or name, substring, case-insensitive. Deliberately not paginated: an
     * admin looking someone up knows roughly who they want, and an unbounded list
     * of every customer is not a feature.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        // Escaped, or a term containing % matches every account.
        $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $validated['term']).'%';

        $accounts = ApiAccount::where('email', 'like', $term)
            ->orWhere('name', 'like', $term)
            ->orderBy('email')
            ->limit(self::SEARCH_LIMIT)
            ->get();

        return response()->json([
            'accounts' => $accounts->map(fn (ApiAccount $account) => [
                'id' => $account->id,
                'name' => $account->name,
                'email' => $account->email,
                'migrated' => $account->hasMigrated(),
                'admin' => $account->isAdmin(),
            ])->all(),
            'truncated' => $accounts->count() === self::SEARCH_LIMIT,
        ]);
    }

    /** Everything the admin needs about one account, in one call. */
    public function account(int $id, PlanService $plans, UsageService $usage, ApiKeyResolver $keys)
    {
        $account = ApiAccount::find($id);

        if ($account === null) {
            return response()->json(['error' => 'No such account.'], 404);
        }

        $subscription = $account->subscription(ApiAccount::SUBSCRIPTION);
        $context = $keys->resolveForAccount($account->id);

        return response()->json([
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'email' => $account->email,
                'email_verified_at' => $account->email_verified_at?->toDateTimeString(),
                'migrated' => $account->hasMigrated(),
                'test_mode' => $account->inTestMode(),
                'receives_test_data' => $account->receivesTestData(),
                'admin' => $account->isAdmin(),
                'terms_accepted_at' => $account->terms_accepted_at?->toDateTimeString(),
                'terms_version_accepted' => $account->terms_version_accepted,
                'stripe_id' => $account->stripe_id,
            ],
            'subscription' => $subscription ? [
                'plan_name' => $this->planName($plans, $subscription->stripe_price),
                'status' => $subscription->stripe_status,
                'on_grace_period' => $subscription->onGracePeriod(),
                'ends_at' => $subscription->ends_at?->toDateString(),
            ] : null,
            // The flags are the reason this page exists: they are granted by hand and
            // have had no UI at all, only direct database edits.
            'flags' => $this->flagsFor($account),
            'granted' => $plans->present($plans->grantedTo($account)),
            'keys' => ApiKey::where('api_account_id', $account->id)
                ->active()
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (ApiKey $key) => ApiKeyController::present($key))
                ->all(),
            'usage' => $usage->forAccount($account),
            'subscription_issue' => $context?->unresolvedMessage(),
        ]);
    }

    /**
     * Comped access. One flag per request rather than a whole map, so a stale page
     * cannot silently revoke a grant the admin never looked at.
     */
    public function setFlag(Request $request, int $id, ApiKeyResolver $keys)
    {
        $validated = $request->validate([
            'flag' => ['required', 'string', 'in:'.implode(',', ApiAccount::APPROVAL_COLUMNS)],
            'value' => ['required', 'boolean'],
        ]);

        $account = ApiAccount::find($id);

        if ($account === null) {
            return response()->json(['error' => 'No such account.'], 404);
        }

        $account->forceFill([$validated['flag'] => $validated['value']])->save();

        // Entitlement is cached alongside the key, so the grant would not take effect
        // until the entry aged out.
        $keys->forgetAccount($account->id);

        return response()->json(['flags' => $this->flagsFor($account)]);
    }

    /**
     * Recent subscription movement, read from `cashier_subscriptions` rather than an
     * activity table of its own — the timestamps already say everything a log would.
     */
    public function activity()
    {
        $rows = CashierSubscription::query()
            ->orderByDesc('updated_at')
            ->limit(self::ACTIVITY_LIMIT)
            ->get();

        $accounts = ApiAccount::whereIn('id', $rows->pluck('user_id')->unique())
            ->get()
            ->keyBy('id');

        return response()->json([
            'activity' => $rows->map(function (CashierSubscription $row) use ($accounts) {
                $account = $accounts->get($row->user_id);

                return [
                    'id' => $row->id,
                    'name' => $account?->name,
                    'email' => $account?->email,
                    'status' => $row->stripe_status,
                    'started_at' => $row->created_at?->toDateTimeString(),
                    'changed_at' => $row->updated_at?->toDateTimeString(),
                    'ends_at' => $row->ends_at?->toDateTimeString(),
                ];
            })->all(),
        ]);
    }

    /** Headline counts. Cheap aggregates only — nothing here scans a replay table. */
    public function metrics()
    {
        $byStatus = CashierSubscription::query()
            ->select('stripe_status', DB::raw('count(*) as total'))
            ->groupBy('stripe_status')
            ->pluck('total', 'stripe_status')
            ->all();

        return response()->json([
            'accounts' => ApiAccount::count(),
            'migrated' => ApiAccount::where('migrated', true)->count(),
            'subscriptions_by_status' => $byStatus,
            'active_keys' => ApiKey::whereNull('revoked_at')->count(),
        ]);
    }

    /** Tier name for a Stripe price, or the raw price when it is one we no longer list. */
    private function planName(PlanService $plans, ?string $stripePrice): ?string
    {
        $planId = $plans->planIdForPrice($stripePrice);

        return $planId === null
            ? $stripePrice
            : ($plans->all()[$planId]['name'] ?? $stripePrice);
    }

    /** @return array<string, bool> */
    private function flagsFor(ApiAccount $account): array
    {
        $flags = [];

        foreach (ApiAccount::APPROVAL_COLUMNS as $column) {
            $flags[$column] = (bool) $account->{$column};
        }

        return $flags;
    }
}
