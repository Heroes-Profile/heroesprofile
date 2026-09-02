<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Account\ApiKeyController;
use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use App\Models\Api\ApiAccountAction;
use App\Models\Api\ApiKey;
use App\Models\Api\CashierSubscription;
use App\Services\Api\AccountEnforcementService;
use App\Services\Api\ApiKeyResolver;
use App\Services\Api\PlanService;
use App\Services\Api\UsageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminConsoleController extends Controller
{
    private const SEARCH_LIMIT = 25;

    private const ACTIVITY_LIMIT = 50;

    private const HISTORY_LIMIT = 50;

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
                // Whatever they typed. The console links it only if it already looks
                // like an http(s) address, and shows it as text otherwise.
                'website' => $account->website,
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
        ] + $this->standing($account));
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
     * Notice, not enforcement. Their keys keep working — this is the rung for a
     * licence condition being missed rather than abused, and the row it writes is
     * what makes a later suspension defensible.
     */
    public function warn(Request $request, int $id, AccountEnforcementService $enforcement)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'respond_by' => ['nullable', 'date', 'after:today'],
        ]);

        $account = $this->target($id);

        if (! $account instanceof ApiAccount) {
            return $account;
        }

        $enforcement->warn(
            $account,
            $validated['reason'],
            $validated['notes'] ?? null,
            isset($validated['respond_by']) ? Carbon::parse($validated['respond_by']) : null,
            $this->actorId(),
        );

        return response()->json($this->standing($account->refresh()));
    }

    /** Reversible. Keys stop working on both sites; the subscription keeps running. */
    public function suspend(Request $request, int $id, AccountEnforcementService $enforcement)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $account = $this->target($id);

        if (! $account instanceof ApiAccount) {
            return $account;
        }

        $enforcement->suspend($account, $validated['reason'], $validated['notes'] ?? null, $this->actorId());

        return response()->json($this->standing($account->refresh()));
    }

    /**
     * Permanent, and it cancels their subscription. Not reachable by passing a string
     * to the suspend route for exactly that reason.
     */
    public function terminate(Request $request, int $id, AccountEnforcementService $enforcement)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $account = $this->target($id);

        if (! $account instanceof ApiAccount) {
            return $account;
        }

        $enforcement->terminate($account, $validated['reason'], $validated['notes'] ?? null, $this->actorId());

        return response()->json($this->standing($account->refresh()));
    }

    public function reinstate(Request $request, int $id, AccountEnforcementService $enforcement)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $account = $this->target($id);

        if (! $account instanceof ApiAccount) {
            return $account;
        }

        if (! $account->isSuspended()) {
            return response()->json(['error' => 'That account is not suspended.'], 409);
        }

        $enforcement->reinstate($account, $validated['notes'] ?? null, $this->actorId());

        return response()->json($this->standing($account->refresh()));
    }

    /**
     * The account an action names, or the response explaining why it cannot be
     * actioned. Admins are refused: suspending one locks the console's own door, and
     * an admin who needs disciplining needs their grant removed, not their key.
     */
    private function target(int $id): ApiAccount|JsonResponse
    {
        $account = ApiAccount::find($id);

        if ($account === null) {
            return response()->json(['error' => 'No such account.'], 404);
        }

        if ($account->isAdmin()) {
            return response()->json(['error' => 'Administrator accounts cannot be actioned here.'], 422);
        }

        return $account;
    }

    /** The admin pressing the button, recorded on every action. */
    private function actorId(): ?int
    {
        return Auth::guard('api_web')->id();
    }

    /**
     * Where the account stands and how it got there. Returned by every action so the
     * console shows the result without a second fetch.
     */
    private function standing(ApiAccount $account): array
    {
        $history = ApiAccountAction::where('api_account_id', $account->id)
            ->orderByDesc('created_at')
            ->limit(self::HISTORY_LIMIT)
            ->get();

        $actors = ApiAccount::whereIn('id', $history->pluck('performed_by')->filter()->unique())
            ->pluck('name', 'id');

        $warning = $account->unacknowledgedWarning();

        return [
            'enforcement' => [
                'suspended' => $account->isSuspended(),
                'terminated' => $account->isTerminated(),
                'reason' => $account->suspension_reason,
                'since' => $account->suspended_at?->toDateTimeString(),
                // Open means sent and not yet dismissed. Overdue is informational —
                // nothing escalates without someone pressing a button.
                'open_warning' => $warning === null ? null : [
                    'reason' => $warning->reason,
                    'respond_by' => $warning->respond_by?->toDateString(),
                    'overdue' => $warning->isOverdue(),
                    'sent_at' => $warning->created_at?->toDateTimeString(),
                ],
            ],
            'history' => $history->map(fn (ApiAccountAction $row) => [
                'id' => $row->id,
                'action' => $row->action,
                'reason' => $row->reason,
                'notes' => $row->notes,
                'respond_by' => $row->respond_by?->toDateString(),
                'acknowledged_at' => $row->acknowledged_at?->toDateTimeString(),
                'by' => $actors[$row->performed_by] ?? null,
                'at' => $row->created_at?->toDateTimeString(),
            ])->all(),
        ];
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
    public function metrics(PlanService $plans)
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
            'active_subscribers' => $this->activeSubscribers(),
            'mrr' => $this->monthlyRevenue($plans),
        ]);
    }

    /**
     * Accounts entitled to live data right now, cancellations still inside their paid
     * period included.
     *
     * Same rule ApiKeyResolver applies per call, so this figure and what the API
     * actually honours cannot drift apart. Distinct accounts rather than rows: a swap
     * reuses the row, but nothing stops an account holding more than one.
     */
    private function activeSubscribers(): int
    {
        return $this->entitled()->distinct()->count('user_id');
    }

    /**
     * Monthly recurring revenue, whole dollars.
     *
     * Narrower than the subscriber count on purpose — a cancellation riding out its
     * paid period is entitled today but renews nothing, so a set `ends_at` drops it.
     * Trialing stays in: it converts on its own, and no trial is sold here anyway.
     *
     * Amounts come from PlanService, which reads Stripe's own unit_amount and caches
     * it per price. A price we no longer list resolves to no plan and adds nothing.
     */
    private function monthlyRevenue(PlanService $plans): int
    {
        $byPrice = $this->entitled()
            ->whereNull('ends_at')
            ->select('stripe_price', DB::raw('count(distinct user_id) as total'))
            ->groupBy('stripe_price')
            ->pluck('total', 'stripe_price');

        $total = 0;

        foreach ($byPrice as $stripePrice => $subscribers) {
            $planId = $plans->planIdForPrice($stripePrice === '' ? null : (string) $stripePrice);

            if ($planId === null) {
                continue;
            }

            $total += (int) $plans->priceFor($planId) * (int) $subscribers;
        }

        return $total;
    }

    /**
     * What Cashier would call valid: our subscription type, a live status, and an
     * `ends_at` that has not passed. `past_due` is out, matching Cashier's default.
     */
    private function entitled(): Builder
    {
        return CashierSubscription::query()
            ->where('type', ApiAccount::SUBSCRIPTION)
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()));
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
