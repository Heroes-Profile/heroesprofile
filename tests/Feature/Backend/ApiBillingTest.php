<?php

namespace Tests\Feature\Backend;

use App\Mail\Api\SubscriptionActivity;
use App\Models\Api\ApiAccount;
use App\Notifications\Api\SubscriptionCancelled;
use App\Notifications\Api\SubscriptionStarted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FakeStripe;
use Tests\Support\SignsInApiAccounts;

/**
 * Billing through Cashier with Stripe's API faked underneath it, and Stripe's webhook
 * delivered with a real signature.
 */
class ApiBillingTest extends FeatureTestCase
{
    use SignsInApiAccounts;

    private const WEBHOOK_SECRET = 'whsec_test_secret';

    private FakeStripe $stripe;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cashier.key' => 'pk_test_fake', 'cashier.secret' => 'sk_test_fake',
            'cashier.webhook.secret' => self::WEBHOOK_SECRET, 'mail.admin_address' => 'admin@heroesprofile.test',
        ]);
        Notification::fake();
        Mail::fake();

        $this->stripe = FakeStripe::install();
        foreach (config('api_plans.plans') as $plan) {
            $this->stripe->prices[$plan['stripe_price']] = $plan['price'] * 100;
        }

        // What Stripe holds for the seeded subscribers
        foreach (['basic' => 1, 'developer' => 3, 'admin' => 3] as $slug => $planId) {
            $this->stripe->addCustomer('cus_local_'.$slug);
            $this->stripe->addSubscription('sub_local_'.$slug, 'cus_local_'.$slug, config("api_plans.plans.{$planId}.stripe_price"));
        }
    }

    protected function tearDown(): void
    {
        FakeStripe::uninstall();

        parent::tearDown();
    }

    private function ok($response)
    {
        $this->assertSame(200, $response->getStatusCode(), substr((string) $response->getContent(), 0, 1500));

        return $response;
    }

    private function subscriptionRow(ApiAccount $account): object
    {
        return DB::connection('heroesprofile_api')->table('cashier_subscriptions')
            ->where('user_id', $account->id)->orderByDesc('id')->first();
    }

    public function test_the_billing_page_shows_the_price_stripe_holds(): void
    {
        $this->stripe->prices[config('api_plans.plans.1.stripe_price')] = 700;

        $this->asApiAccount('fixtures')->get('/Api/Account/Billing')->assertOk()->assertViewHas('plans', function ($plans) {
            return collect($plans)->firstWhere('id', 1)['price'] === 7;
        });
        $this->assertContains('GET /v1/prices/'.config('api_plans.plans.1.stripe_price'), array_map(fn ($r) => $r['method'].' '.$r['path'], $this->stripe->requests));
    }

    public function test_a_setup_intent_is_created_for_the_card_form(): void
    {
        $this->asApiAccount('basic')->postJson('/api/v1/account/billing/setup-intent')
            ->assertOk()->assertExactJson(['client_secret' => 'seti_secret_test']);

        $this->assertContains('POST /v1/setup_intents', $this->stripe->calls());
    }

    public function test_saving_a_card_makes_it_the_default_and_stores_its_brand(): void
    {
        $this->stripe->addPaymentMethod('pm_card_mastercard', 'mastercard', '4444');

        $this->asApiAccount('basic')->postJson('/api/v1/account/billing/payment-method', ['payment_method' => 'pm_card_mastercard'])
            ->assertOk()->assertExactJson(['payment_method' => ['brand' => 'mastercard', 'last_four' => '4444']]);

        $account = $this->apiAccount('basic');
        $this->assertSame(['mastercard', '4444'], [$account->pm_type, $account->pm_last_four]);
        $this->assertSame('pm_card_mastercard', $this->stripe->customers['cus_local_basic']['invoice_settings']['default_payment_method']);
        $this->assertSame('cus_local_basic', $this->stripe->paymentMethods['pm_card_mastercard']['customer']);
    }

    public function test_subscribing_creates_the_customer_and_the_subscription(): void
    {
        $account = $this->apiAccount('fixtures');
        $this->stripe->addPaymentMethod('pm_card_visa');

        $this->asApiAccount($account)->postJson('/api/v1/account/billing/payment-method', ['payment_method' => 'pm_card_visa'])->assertOk();
        $this->ok($this->postJson('/api/v1/account/billing/subscribe', ['plan_id' => 1]))->assertExactJson(['ok' => true]);

        $account->refresh();
        $this->assertStringStartsWith('cus_fake', $account->stripe_id);
        $row = $this->subscriptionRow($account);
        $this->assertSame(['active', config('api_plans.plans.1.stripe_price')], [$row->stripe_status, $row->stripe_price]);
        $this->assertTrue($account->subscribed(ApiAccount::SUBSCRIPTION));

        $created = collect($this->stripe->requests)->firstWhere('path', '/v1/subscriptions');
        $this->assertSame($account->stripe_id, $created['params']['customer']);
        $this->assertSame('pm_card_visa', $this->stripe->customers[$account->stripe_id]['invoice_settings']['default_payment_method']);
    }

    public function test_subscribing_again_swaps_the_price(): void
    {
        $account = $this->apiAccount('basic');

        $this->ok($this->asApiAccount($account)->postJson('/api/v1/account/billing/subscribe', ['plan_id' => 2]));

        $this->assertSame(config('api_plans.plans.2.stripe_price'), $this->subscriptionRow($account)->stripe_price);
        $this->assertSame(config('api_plans.plans.2.stripe_price'), $this->stripe->subscriptions['sub_local_basic']['items']['data'][0]['price']['id']);
    }

    public function test_developer_needs_approval_and_a_project_is_required(): void
    {
        $this->asApiAccount('basic')->postJson('/api/v1/account/billing/subscribe', ['plan_id' => 3])
            ->assertForbidden()->assertJson(['error' => 'That plan is not available on your account.']);

        $account = $this->apiAccount('fixtures');
        $account->forceFill(['project_name' => null, 'project_description' => null])->save();
        $this->asApiAccount($account)->postJson('/api/v1/account/billing/subscribe', ['plan_id' => 1])->assertUnprocessable();

        $this->assertNotContains('POST /v1/subscriptions', $this->stripe->calls());
    }

    public function test_cancelling_runs_to_period_end_and_resuming_undoes_it(): void
    {
        $account = $this->apiAccount('developer');

        $this->ok($this->asApiAccount($account)->postJson('/api/v1/account/billing/cancel'));
        $this->assertTrue($this->stripe->subscriptions['sub_local_developer']['cancel_at_period_end']);
        $subscription = $account->fresh()->subscription(ApiAccount::SUBSCRIPTION);
        $this->assertTrue($subscription->onGracePeriod());
        $this->assertSame(now()->addMonth()->toDateString(), $subscription->ends_at->toDateString());

        $this->ok($this->postJson('/api/v1/account/billing/resume'));
        $this->assertFalse($this->stripe->subscriptions['sub_local_developer']['cancel_at_period_end']);
        $this->assertNull($account->fresh()->subscription(ApiAccount::SUBSCRIPTION)->ends_at);

        $this->postJson('/api/v1/account/billing/resume')->assertNotFound();
    }

    public function test_cancelling_without_a_subscription_is_a_404(): void
    {
        $this->asApiAccount('fixtures')->postJson('/api/v1/account/billing/cancel')->assertNotFound();
    }

    public function test_a_stripe_error_is_reported_back(): void
    {
        $this->stripe->failNext = [402, 'Your card was declined.'];

        $this->asApiAccount('basic')->postJson('/api/v1/account/billing/subscribe', ['plan_id' => 2])
            ->assertUnprocessable()->assertJson(['error' => 'Stripe: Your card was declined.']);

        $this->assertSame(config('api_plans.plans.1.stripe_price'), $this->subscriptionRow($this->apiAccount('basic'))->stripe_price);
    }

    private function webhook(string $type, array $object, array $previous = [], ?string $secret = self::WEBHOOK_SECRET)
    {
        $payload = json_encode([
            'id' => 'evt_test_'.md5($type.json_encode($object)), 'object' => 'event', 'type' => $type,
            'api_version' => '2025-03-31.basil', 'created' => time(),
            'data' => ['object' => $object] + ($previous ? ['previous_attributes' => $previous] : []),
        ]);
        // Stripe signs with the real clock, whatever the test's clock says
        $timestamp = time();
        $signature = 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return $this->call('POST', '/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $signature,
        ], $payload);
    }

    public function test_a_subscription_made_in_stripe_arrives_by_webhook_and_mails_the_customer_and_admin(): void
    {
        $account = $this->apiAccount('fixtures');
        $account->forceFill(['stripe_id' => 'cus_from_old_site'])->save();
        $object = $this->stripe->addSubscription('sub_from_old_site', 'cus_from_old_site', config('api_plans.plans.2.stripe_price'));

        $this->webhook('customer.subscription.created', $object)->assertOk();

        $row = $this->subscriptionRow($account);
        $this->assertSame(['sub_from_old_site', 'active'], [$row->stripe_id, $row->stripe_status]);
        Notification::assertSentTo($account, SubscriptionStarted::class);
        Mail::assertSent(SubscriptionActivity::class, fn ($mail) => $mail->hasTo('admin@heroesprofile.test'));
    }

    public function test_a_scheduled_cancellation_by_webhook_mails_once_and_the_end_mails_nothing_more(): void
    {
        $account = $this->apiAccount('developer');
        $object = $this->stripe->addSubscription('sub_local_developer', 'cus_local_developer', config('api_plans.plans.3.stripe_price'), [
            'cancel_at_period_end' => true, 'canceled_at' => now()->getTimestamp(),
        ]);

        $this->ok($this->webhook('customer.subscription.updated', $object, ['cancel_at_period_end' => false]));
        Notification::assertSentToTimes($account, SubscriptionCancelled::class, 1);
        $this->assertNotNull($this->subscriptionRow($account)->ends_at);

        $object['status'] = 'canceled';
        $this->webhook('customer.subscription.deleted', $object)->assertOk();
        Notification::assertSentToTimes($account, SubscriptionCancelled::class, 1);
        $this->assertSame('canceled', $this->subscriptionRow($account)->stripe_status);
    }

    public function test_a_price_change_by_webhook_is_mailed_as_a_plan_change(): void
    {
        $account = $this->apiAccount('basic');
        $object = $this->stripe->addSubscription('sub_local_basic', 'cus_local_basic', config('api_plans.plans.2.stripe_price'));

        $this->webhook('customer.subscription.updated', $object, [
            'items' => ['data' => [['price' => ['id' => config('api_plans.plans.1.stripe_price')]]]],
        ])->assertOk();

        Notification::assertSentTo($account, SubscriptionStarted::class);
        Mail::assertSent(SubscriptionActivity::class);
        $this->assertSame(config('api_plans.plans.2.stripe_price'), $this->subscriptionRow($account)->stripe_price);
    }

    public function test_a_webhook_with_a_bad_signature_is_refused(): void
    {
        $object = $this->stripe->addSubscription('sub_forged', 'cus_local_basic', config('api_plans.plans.3.stripe_price'));

        $this->webhook('customer.subscription.created', $object, [], 'whsec_wrong')->assertForbidden();

        Notification::assertNothingSent();
        $this->assertFalse(DB::connection('heroesprofile_api')->table('cashier_subscriptions')->where('stripe_id', 'sub_forged')->exists());
    }

    public function test_the_webhook_is_refused_when_no_secret_is_configured(): void
    {
        config(['cashier.webhook.secret' => null]);

        $this->webhook('customer.subscription.created', ['customer' => 'cus_local_basic'])->assertStatus(500);
    }
}
