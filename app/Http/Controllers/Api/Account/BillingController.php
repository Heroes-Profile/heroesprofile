<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use App\Services\Api\ApiKeyResolver;
use App\Services\Api\PlanService;
use App\Services\Api\UsageService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Throwable;

class BillingController extends Controller
{
    private const SUBSCRIPTION = ApiAccount::SUBSCRIPTION;

    public function show(PlanService $plans, UsageService $usage, ApiKeyResolver $keys)
    {
        $account = $this->account();
        $subscription = $account->subscription(self::SUBSCRIPTION);
        $context = $keys->resolveForAccount($account->id);

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
            'servesFixtures' => (bool) $context?->servesFixtures(),
            // Their key is being refused. They will otherwise only meet this as a
            // 403 inside their own integration, where nobody is looking.
            'subscriptionIssue' => $context?->unresolvedMessage(),
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

    public function subscribe(Request $request, PlanService $plans, ApiKeyResolver $keys)
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

        return $this->withBillingLock($account, function () use ($account, $price, $keys) {
            // Re-read inside the lock. The relation is cached on the model, and the
            // whole point of the lock is that the answer may have changed between
            // the request arriving and getting here.
            $account->unsetRelation('subscriptions');

            try {
                if ($account->subscribed(self::SUBSCRIPTION)) {
                    $account->subscription(self::SUBSCRIPTION)->swap($price);
                } else {
                    $account->newSubscription(self::SUBSCRIPTION, $price)->create(
                        $account->defaultPaymentMethod()?->id
                    );
                }
            } catch (IncompletePayment $e) {
                // The subscription exists but is unpaid pending authentication.
                // Entitlement follows `stripe_status`, so it is already correct;
                // what matters is that the customer gets a way to finish rather
                // than a message telling them Stripe said no.
                $keys->forgetAccount($account->id);

                return response()->json([
                    'requires_action' => true,
                    'client_secret' => $e->payment->clientSecret(),
                    'error' => 'Your bank needs to confirm this payment.',
                ], 402);
            } catch (Throwable $e) {
                return $this->stripeError($e);
            }

            $keys->forgetAccount($account->id);

            return response()->json(['ok' => true]);
        });
    }

    public function cancel(ApiKeyResolver $keys)
    {
        $account = $this->account();

        if (! $account->subscribed(self::SUBSCRIPTION)) {
            return response()->json(['error' => 'You have no active subscription.'], 404);
        }

        return $this->withBillingLock($account, function () use ($account, $keys) {
            try {
                // At period end, so they keep access they have already paid for.
                $account->subscription(self::SUBSCRIPTION)->cancel();
            } catch (Throwable $e) {
                return $this->stripeError($e);
            }

            $keys->forgetAccount($account->id);

            return response()->json(['ok' => true]);
        });
    }

    public function resume(ApiKeyResolver $keys)
    {
        $account = $this->account();
        $subscription = $account->subscription(self::SUBSCRIPTION);

        if (! $subscription || ! $subscription->onGracePeriod()) {
            return response()->json(['error' => 'Nothing to resume.'], 404);
        }

        return $this->withBillingLock($account, function () use ($account, $subscription, $keys) {
            try {
                $subscription->resume();
            } catch (Throwable $e) {
                return $this->stripeError($e);
            }

            $keys->forgetAccount($account->id);

            return response()->json(['ok' => true]);
        });
    }

    /**
     * Serialises billing changes for one account.
     *
     * `subscribe()` is check-then-act: it asks whether a subscription exists, then
     * creates one. Two requests arriving together — a double click, a retry after a
     * timeout, two tabs — both see "none" and both create, and the customer is billed
     * twice. Cancel and resume are held under the same lock so they cannot interleave
     * with a swap either.
     */
    private function withBillingLock(ApiAccount $account, Closure $work)
    {
        $lock = Cache::lock('api-billing:'.$account->id, 15);

        if (! $lock->get()) {
            return response()->json([
                'error' => 'Another billing change is still going through. Give it a moment and try again.',
            ], 409);
        }

        try {
            return $work();
        } finally {
            $lock->release();
        }
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
