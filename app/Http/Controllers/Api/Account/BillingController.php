<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use App\Services\Api\ApiKeyResolver;
use App\Services\Api\PlanService;
use App\Services\Api\UsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class BillingController extends Controller
{
    private const SUBSCRIPTION = ApiAccount::SUBSCRIPTION;

    public function show(PlanService $plans, UsageService $usage, ApiKeyResolver $keys)
    {
        $account = $this->account();
        $subscription = $account->subscription(self::SUBSCRIPTION);

        return view('api.account.billing', [
            'stripeKey' => config('cashier.key'),
            'plans' => $plans->present($plans->selectableBy($account)),
            'granted' => $plans->present($plans->grantedTo($account)),
            'subscription' => $subscription ? [
                'plan_id' => $plans->planIdForPrice($subscription->stripe_price),
                'stripe_price' => $subscription->stripe_price,
                'status' => $subscription->stripe_status,
                'on_grace_period' => $subscription->onGracePeriod(),
                'ends_at' => $subscription->ends_at?->toDateString(),
                'cancelled' => $subscription->canceled(),
            ] : null,
            'paymentMethod' => $account->hasDefaultPaymentMethod() ? [
                'brand' => $account->pm_type,
                'last_four' => $account->pm_last_four,
            ] : null,
            'usage' => $usage->forAccount($account),
            // Paying while on fixtures is legitimate — someone can subscribe with
            // test mode still on — but it should never be a surprise.
            'servesFixtures' => (bool) $keys->resolveForAccount($account->id)?->servesFixtures(),
        ]);
    }

    /** Client secret for the embedded Payment Element. */
    public function setupIntent()
    {
        return response()->json([
            'client_secret' => $this->account()->createSetupIntent()->client_secret,
        ]);
    }

    public function savePaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string'],
        ]);

        $account = $this->account();

        try {
            $account->createOrGetStripeCustomer();
            $account->updateDefaultPaymentMethod($validated['payment_method']);
        } catch (Throwable $e) {
            return $this->stripeError($e);
        }

        return response()->json([
            'payment_method' => [
                'brand' => $account->pm_type,
                'last_four' => $account->pm_last_four,
            ],
        ]);
    }

    public function subscribe(Request $request, PlanService $plans)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer'],
        ]);

        $account = $this->account();
        $selectable = $plans->selectableBy($account);

        $plan = $selectable[$validated['plan_id']] ?? null;

        if ($plan === null || ! $plan['purchasable']) {
            return response()->json([
                'error' => 'That plan is not available on your account.',
            ], 403);
        }

        $price = $plan['stripe_price'];

        try {
            if ($account->subscribed(self::SUBSCRIPTION)) {
                $account->subscription(self::SUBSCRIPTION)->swap($price);
            } else {
                $account->newSubscription(self::SUBSCRIPTION, $price)->create(
                    $account->defaultPaymentMethod()?->id
                );
            }
        } catch (Throwable $e) {
            return $this->stripeError($e);
        }

        return response()->json(['ok' => true]);
    }

    public function cancel()
    {
        $account = $this->account();

        if (! $account->subscribed(self::SUBSCRIPTION)) {
            return response()->json(['error' => 'You have no active subscription.'], 404);
        }

        try {
            // At period end, so they keep access they have already paid for.
            $account->subscription(self::SUBSCRIPTION)->cancel();
        } catch (Throwable $e) {
            return $this->stripeError($e);
        }

        return response()->json(['ok' => true]);
    }

    public function resume()
    {
        $account = $this->account();
        $subscription = $account->subscription(self::SUBSCRIPTION);

        if (! $subscription || ! $subscription->onGracePeriod()) {
            return response()->json(['error' => 'Nothing to resume.'], 404);
        }

        try {
            $subscription->resume();
        } catch (Throwable $e) {
            return $this->stripeError($e);
        }

        return response()->json(['ok' => true]);
    }

    public function invoices()
    {
        try {
            $invoices = $this->account()->invoices();
        } catch (Throwable $e) {
            return $this->stripeError($e);
        }

        return response()->json([
            'invoices' => collect($invoices)->map(fn ($invoice) => [
                'id' => $invoice->id,
                'date' => $invoice->date()->toDateString(),
                'total' => $invoice->total(),
            ])->all(),
        ]);
    }

    private function account(): ApiAccount
    {
        return Auth::guard('api_web')->user();
    }

    private function stripeError(Throwable $e)
    {
        report($e);

        return response()->json([
            'error' => config('app.env') === 'production'
                ? 'Stripe rejected that request. Please try again, or contact support if it persists.'
                : 'Stripe: '.$e->getMessage(),
        ], 422);
    }
}
