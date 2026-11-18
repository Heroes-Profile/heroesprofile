<?php

namespace Tests\Feature\Backend;

use App\Http\Controllers\Api\Admin\ImpersonationController;
use App\Models\Api\ApiAccount;
use App\Models\Api\ApiAccountAction;
use App\Models\Poll;
use App\Notifications\Api\AccountInfoRequested;
use App\Notifications\Api\AccountReinstated;
use App\Notifications\Api\AccountSuspended;
use App\Notifications\Api\AccountTerminated;
use App\Notifications\Api\AccountWarned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FakeStripe;
use Tests\Support\SignsInApiAccounts;

/** The API admin console's writes: grants, the enforcement ladder (with Stripe faked), impersonation and polls. */
class ApiAdminConsoleTest extends FeatureTestCase
{
    use SignsInApiAccounts;

    private FakeStripe $stripe;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cashier.secret' => 'sk_test_fake']);
        Notification::fake();
        $this->stripe = FakeStripe::install();
        $this->stripe->addCustomer('cus_local_developer');
        $this->stripe->addSubscription('sub_local_developer', 'cus_local_developer', config('api_plans.plans.3.stripe_price'));

        $this->asApiAccount('admin');
    }

    protected function tearDown(): void
    {
        FakeStripe::uninstall();

        parent::tearDown();
    }

    private function url(string $slug, string $action): string
    {
        return '/api/v1/admin/accounts/'.$this->apiAccount($slug)->id.'/'.$action;
    }

    private function actions(ApiAccount $account): array
    {
        return ApiAccountAction::where('api_account_id', $account->id)->orderBy('id')->pluck('action')->all();
    }

    public function test_the_console_is_admin_only(): void
    {
        $this->asApiAccount('developer')->postJson('/api/v1/admin/accounts/search', ['term' => 'basic'])->assertForbidden();
    }

    public function test_searching_accounts_by_email_name_or_id(): void
    {
        $this->postJson('/api/v1/admin/accounts/search', ['term' => 'basic@'])->assertOk()
            ->assertJsonPath('accounts.0.email', 'basic@heroesprofile.test')->assertJsonCount(1, 'accounts');

        $id = $this->apiAccount('partner')->id;
        $this->postJson('/api/v1/admin/accounts/search', ['term' => (string) $id])->assertOk()->assertJsonPath('accounts.0.id', $id);

        // A bare % is escaped rather than matching everyone
        $this->postJson('/api/v1/admin/accounts/search', ['term' => '%%'])->assertOk()->assertJsonCount(0, 'accounts');
    }

    public function test_granting_a_flag_records_an_approval(): void
    {
        $this->postJson($this->url('basic', 'flag'), ['flag' => 'p_approved', 'value' => true])->assertOk()
            ->assertJsonPath('approvals.0.type', 'flag');

        $this->assertTrue((bool) $this->apiAccount('basic')->p_approved);

        $this->postJson($this->url('basic', 'flag'), ['flag' => 'admin', 'value' => true])->assertUnprocessable();
    }

    public function test_approving_a_project_and_editing_the_notes(): void
    {
        $approval = $this->postJson($this->url('basic', 'approve'), ['notes' => 'Looks fine'])->assertOk()->json('approvals.0');
        $this->assertSame('Looks fine', $approval['notes']);

        $this->postJson($this->url('basic', 'approvals/'.$approval['id'].'/notes'), ['notes' => 'Checked the repo'])->assertOk()
            ->assertJsonPath('approvals.0.notes', 'Checked the repo');
    }

    public function test_poison_markers_are_toggled_and_logged(): void
    {
        $this->postJson($this->url('basic', 'poison'), ['value' => true])->assertOk()->assertJsonPath('poison.on', true);
        $this->postJson($this->url('basic', 'poison'), ['value' => true])->assertOk();
        $this->postJson($this->url('basic', 'poison'), ['value' => false])->assertOk()->assertJsonPath('poison.on', false);

        $this->assertSame([ApiAccountAction::POISON_ON, ApiAccountAction::POISON_OFF], $this->actions($this->apiAccount('basic')));
    }

    public function test_warning_and_asking_for_information_email_the_account(): void
    {
        $this->postJson($this->url('basic', 'warn'), ['reason' => '<p>Credit Heroes Profile <script>x</script>on your site.</p>', 'respond_by' => now()->addWeek()->toDateString()])
            ->assertOk()->assertJsonPath('enforcement.open_warning.respond_by', now()->addWeek()->toDateString())
            ->assertJsonPath('enforcement.suspended', false);
        $this->postJson($this->url('basic', 'info'), ['reason' => '<p>What is the project for?</p>'])->assertOk();
        $this->postJson($this->url('basic', 'warn'), ['reason' => '<p> </p>'])->assertUnprocessable()->assertJson(['error' => 'Say what they are being told.']);

        $account = $this->apiAccount('basic');
        Notification::assertSentTo($account, AccountWarned::class);
        Notification::assertSentTo($account, AccountInfoRequested::class);
        $this->assertStringNotContainsString('<script>', ApiAccountAction::where('api_account_id', $account->id)->value('reason'));
    }

    public function test_suspending_then_reinstating(): void
    {
        $this->postJson($this->url('basic', 'suspend'), ['reason' => '<p>Scraping.</p>', 'notes' => 'internal'])->assertOk()
            ->assertJsonPath('enforcement.suspended', true)->assertJsonPath('enforcement.terminated', false);

        $account = $this->apiAccount('basic');
        $this->assertTrue($account->isSuspended());
        Notification::assertSentTo($account, AccountSuspended::class);
        $this->assertSame([], $this->stripe->requests, 'a plain suspension leaves the subscription running');

        $this->postJson($this->url('basic', 'reinstate'))->assertOk()->assertJsonPath('enforcement.suspended', false);
        $this->postJson($this->url('basic', 'reinstate'))->assertStatus(409);
        Notification::assertSentTo($account, AccountReinstated::class);
        $this->assertSame([ApiAccountAction::SUSPEND, ApiAccountAction::REINSTATE], $this->actions($account));
    }

    public function test_suspending_with_a_refund_cancels_now_refunds_the_unused_time_and_drops_developer_approval(): void
    {
        $this->stripe->addSubscription('sub_local_developer', 'cus_local_developer', config('api_plans.plans.3.stripe_price'), [
            'latest_invoice' => 'in_test',
            'items' => ['data' => [['current_period_start' => now()->subDays(10)->getTimestamp(), 'current_period_end' => now()->addDays(30)->getTimestamp()]]],
        ]);
        $this->stripe->invoices['in_test'] = [
            'id' => 'in_test', 'object' => 'invoice', 'amount_paid' => 2500,
            'payments' => ['object' => 'list', 'data' => [['status' => 'paid', 'payment' => ['type' => 'payment_intent', 'payment_intent' => 'pi_test']]]],
        ];

        $response = $this->postJson($this->url('developer', 'suspend'), ['reason' => '<p>Chargeback.</p>', 'refund' => true])->assertOk();

        // 30 of the 40 days were unused
        $this->assertSame(['cancelled' => true, 'refunded' => '$18.75', 'failed' => false], $response->json('refund'));
        $refund = collect($this->stripe->requests)->firstWhere('path', '/v1/refunds');
        $this->assertSame(['payment_intent' => 'pi_test', 'amount' => 1875], array_intersect_key($refund['params'], ['payment_intent' => 1, 'amount' => 1]));
        $this->assertSame('canceled', $this->stripe->subscriptions['sub_local_developer']['status']);

        $account = $this->apiAccount('developer');
        $this->assertFalse((bool) $account->d_approved);
        $this->assertFalse($account->subscribed(ApiAccount::SUBSCRIPTION));
    }

    public function test_terminating_cancels_the_subscription_immediately(): void
    {
        $this->postJson($this->url('developer', 'terminate'), ['reason' => '<p>Resold the data.</p>'])->assertOk()
            ->assertJsonPath('enforcement.terminated', true);

        $this->assertSame(['DELETE /v1/subscriptions/sub_local_developer'], array_values(array_filter(
            array_map(fn ($r) => $r['method'].' '.$r['path'], $this->stripe->requests), fn ($call) => str_starts_with($call, 'DELETE'),
        )));
        Notification::assertSentTo($this->apiAccount('developer'), AccountTerminated::class);
    }

    public function test_a_stripe_outage_still_terminates_the_account(): void
    {
        $this->stripe->failNext = [500, 'Stripe is down'];

        $this->postJson($this->url('developer', 'terminate'), ['reason' => '<p>Resold the data.</p>'])->assertOk();

        $this->assertTrue($this->apiAccount('developer')->isTerminated());
    }

    public function test_admins_cannot_be_actioned_and_notes_change_nothing(): void
    {
        $this->postJson($this->url('admin', 'suspend'), ['reason' => '<p>x</p>'])->assertUnprocessable();

        $this->postJson($this->url('basic', 'note'), ['notes' => 'Emailed them'])->assertOk()->assertJsonPath('history.0.notes', 'Emailed them');
        $this->assertFalse($this->apiAccount('basic')->isSuspended());
        Notification::assertNothingSent();
    }

    public function test_impersonating_swaps_the_session_and_stop_returns_to_the_admin(): void
    {
        $admin = $this->apiAccount('admin');
        $target = $this->apiAccount('basic');

        $this->postJson('/Api/Admin/Impersonate/'.$admin->id)->assertUnprocessable();
        $this->postJson('/Api/Admin/Impersonate/'.$target->id)->assertOk()->assertExactJson(['ok' => true, 'redirect' => '/Api/Account']);

        $this->assertSame($target->id, Auth::guard('api_web')->id());
        $this->assertSame($admin->id, session(ImpersonationController::SESSION_KEY));

        $this->post('/Api/Admin/Impersonate/Stop')->assertRedirect('/Api/Admin');
        $this->assertSame($admin->id, Auth::guard('api_web')->id());
        $this->assertNull(session(ImpersonationController::SESSION_KEY));
    }

    public function test_stop_from_outside_a_swap_only_clears_the_marker(): void
    {
        $this->withSession([ImpersonationController::SESSION_KEY => 1, ImpersonationController::TARGET_SESSION_KEY => 999999])
            ->post('/Api/Admin/Impersonate/Stop')->assertRedirect('/Api/Account');

        $this->assertSame($this->apiAccount('admin')->id, Auth::guard('api_web')->id());
        $this->assertNull(session(ImpersonationController::SESSION_KEY));
    }

    private function pollInput(array $overrides = []): array
    {
        return array_merge([
            'slug' => 'test-poll', 'title' => 'Favourite map?', 'description' => null, 'allow_multiple' => false,
            'closes_at' => now()->addWeek()->toDateTimeString(),
            'options' => [['label' => 'Cursed Hollow'], ['label' => 'Dragon Shire', 'description' => 'The one with the knight']],
        ], $overrides);
    }

    public function test_creating_editing_and_closing_a_poll(): void
    {
        $poll = $this->postJson('/api/v1/admin/polls', $this->pollInput())->assertOk()->json('poll');
        $this->assertSame(['Cursed Hollow', 'Dragon Shire'], array_column($poll['options'], 'label'));

        $this->postJson('/api/v1/admin/polls', $this->pollInput())->assertUnprocessable()->assertJsonValidationErrors('slug');
        $this->postJson('/api/v1/admin/polls', $this->pollInput(['slug' => 'Bad Slug']))->assertUnprocessable();

        $id = Poll::where('slug', 'test-poll')->value('poll_id');
        $this->postJson('/api/v1/admin/polls/'.$id, $this->pollInput(['title' => 'Best map?', 'options' => [['label' => 'A'], ['label' => 'B'], ['label' => 'C']]]))
            ->assertOk()->assertJsonCount(3, 'poll.options');

        $this->postJson('/api/v1/admin/polls/'.$id.'/close')->assertOk();
        $this->assertFalse(Poll::find($id)->isOpen());
    }

    public function test_a_poll_with_votes_can_be_reworded_but_not_reshaped(): void
    {
        $poll = $this->postJson('/api/v1/admin/polls', $this->pollInput())->assertOk()->json('poll');
        $optionIds = array_column($poll['options'], 'id');
        $vote = DB::connection('heroesprofile_polls')->table('poll_votes')->insertGetId([
            'poll_id' => $poll['id'], 'battlenet_accounts_id' => 900001, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::connection('heroesprofile_polls')->table('poll_vote_options')->insert(['poll_vote_id' => $vote, 'poll_option_id' => $optionIds[0]]);

        $this->postJson('/api/v1/admin/polls/'.$poll['id'], $this->pollInput(['options' => [['label' => 'Only one'], ['label' => 'Two']]]))
            ->assertUnprocessable()->assertJsonValidationErrors('options');

        $this->postJson('/api/v1/admin/polls/'.$poll['id'], $this->pollInput(['options' => [
            ['id' => $optionIds[0], 'label' => 'Cursed Hollow (2024)'], ['id' => $optionIds[1], 'label' => 'Dragon Shire'],
        ]]))->assertOk()->assertJsonPath('poll.options.0.label', 'Cursed Hollow (2024)');
    }
}
