<?php

namespace Tests\Feature\Backend;

use App\Models\Api\ApiAccount;
use App\Models\Api\ApiKey;
use App\Notifications\Api\AccountWarned;
use App\Notifications\Api\ResetApiPassword;
use App\Notifications\Api\VerifyApiEmail;
use App\Services\Api\AccountEnforcementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\Feature\FeatureTestCase;
use Tests\Support\SignsInApiAccounts;

/** The API portal's sign-up, sign-in and account settings, end to end against the database. */
class ApiPortalAccountTest extends FeatureTestCase
{
    use SignsInApiAccounts;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_registering_creates_a_live_account_signs_it_in_and_sends_verification(): void
    {
        $this->post('/Api/Register', [
            'name' => 'New Developer', 'email' => 'new@heroesprofile.test',
            'password' => 'correct-horse', 'password_confirmation' => 'correct-horse', 'terms' => '1',
        ])->assertRedirect('/Api/Account');

        $account = ApiAccount::where('email', 'new@heroesprofile.test')->firstOrFail();
        $this->assertTrue((bool) $account->migrated);
        $this->assertSame(config('api.terms_version'), $account->terms_version_accepted);
        $this->assertTrue(Hash::check('correct-horse', $account->password));
        $this->assertSame($account->id, Auth::guard('api_web')->id());
        Notification::assertSentTo($account, VerifyApiEmail::class);
    }

    public function test_registering_needs_a_new_email_matching_passwords_and_the_terms(): void
    {
        $this->post('/Api/Register', [
            'name' => 'Dupe', 'email' => 'developer@heroesprofile.test',
            'password' => 'correct-horse', 'password_confirmation' => 'different', 'terms' => '',
        ])->assertSessionHasErrors(['email', 'password', 'terms']);

        $this->assertSame(1, ApiAccount::where('email', 'developer@heroesprofile.test')->count());
        Notification::assertNothingSent();
    }

    public function test_logging_in_and_out(): void
    {
        $this->post('/Api/Login', ['email' => 'developer@heroesprofile.test', 'password' => 'password'])
            ->assertRedirect('/Api/Account');
        $this->assertSame($this->apiAccount('developer')->id, Auth::guard('api_web')->id());

        $this->post('/Api/Logout')->assertRedirect('/Api');
        $this->assertNull(Auth::guard('api_web')->user());
    }

    public function test_a_wrong_password_is_refused_and_five_lock_the_email_out(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/Api/Login', ['email' => 'developer@heroesprofile.test', 'password' => 'wrong'])
                ->assertSessionHasErrors(['email' => 'Those credentials do not match our records.']);
        }

        $response = $this->post('/Api/Login', ['email' => 'developer@heroesprofile.test', 'password' => 'password']);
        $this->assertStringStartsWith('Too many login attempts.', session('errors')->first('email'));
        $this->assertNull(Auth::guard('api_web')->user());
        $response->assertRedirect();
    }

    public function test_a_password_reset_link_is_mailed_and_the_token_sets_a_new_password(): void
    {
        $account = $this->apiAccount('basic');

        $this->post('/Api/Password/Forgot', ['email' => $account->email])->assertSessionHas('status');
        Notification::assertSentTo($account, ResetApiPassword::class);

        // Unknown addresses get the same answer
        $this->post('/Api/Password/Forgot', ['email' => 'nobody@heroesprofile.test'])->assertSessionHas('status');

        $token = Password::broker('api_accounts')->createToken($account);
        $this->post('/Api/Password/Reset', [
            'token' => $token, 'email' => $account->email,
            'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass',
        ])->assertRedirect('/Api/Login');

        $this->assertTrue(Hash::check('brand-new-pass', $account->fresh()->password));

        // Reset and forgot share the 3-a-minute contact limit
        $bogus = [
            'token' => 'not-a-token', 'email' => $account->email,
            'password' => 'another-pass', 'password_confirmation' => 'another-pass',
        ];
        $this->post('/Api/Password/Reset', $bogus)->assertTooManyRequests();
        $this->travel(61)->seconds();

        $this->post('/Api/Password/Reset', [
            'token' => 'not-a-token', 'email' => $account->email,
            'password' => 'another-pass', 'password_confirmation' => 'another-pass',
        ])->assertSessionHasErrors('email');
    }

    public function test_accepting_new_terms_stamps_the_version(): void
    {
        $account = $this->apiAccount('basic');
        $account->forceFill(['terms_version_accepted' => '2020-01-01'])->save();

        $this->asApiAccount($account)->post('/Api/Terms/Accept')->assertRedirect('/Api/Account');

        $this->assertSame(config('api.terms_version'), $account->fresh()->terms_version_accepted);
    }

    public function test_account_details_are_saved(): void
    {
        $this->asApiAccount('basic');

        $this->postJson('/api/v1/account/name', ['name' => '  Renamed  '])->assertExactJson(['name' => 'Renamed']);
        $this->postJson('/api/v1/account/header-display', ['header_display' => 'name'])->assertExactJson(['header_display' => 'name']);
        $this->postJson('/api/v1/account/header-display', ['header_display' => 'everything'])->assertUnprocessable();

        $this->postJson('/api/v1/account/project', ['project_name' => 'Bot', 'project_description' => 'Too short'])
            ->assertUnprocessable()->assertJsonValidationErrors('project_description');
        $this->postJson('/api/v1/account/project', [
            'project_name' => ' Draft Bot ', 'project_description' => str_repeat('Suggests drafts for a Discord server. ', 2), 'website' => '',
        ])->assertOk()->assertJson(['project_name' => 'Draft Bot', 'website' => null]);

        $account = $this->apiAccount('basic');
        $this->assertSame(['Renamed', 'name', 'Draft Bot'], [$account->name, $account->header_display, $account->project_name]);
    }

    public function test_changing_the_password_needs_the_current_one(): void
    {
        $this->asApiAccount('basic');

        $this->postJson('/api/v1/account/password', ['current_password' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertUnprocessable()->assertJsonPath('errors.current_password.0', 'Your current password is not correct.');

        $this->postJson('/api/v1/account/password', ['current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertExactJson(['changed' => true]);

        $this->assertTrue(Hash::check('new-password', $this->apiAccount('basic')->password));
    }

    public function test_keys_are_created_shown_once_and_revoked(): void
    {
        $account = $this->apiAccount('basic');
        $this->asApiAccount($account);

        $created = $this->postJson('/api/v1/account/keys', ['name' => 'CI'])->assertOk()->json('key');
        $this->assertSame('CI', $created['name']);
        $this->assertNotEmpty($created['plain_text']);

        $key = ApiKey::findOrFail($created['id']);
        $this->assertSame($account->id, $key->api_account_id);
        $this->assertNotSame($created['plain_text'], $key->secret_hash, 'only a hash is stored');

        $this->postJson('/api/v1/account/keys/revoke', ['id' => $key->id])->assertExactJson(['revoked' => $key->id]);
        $this->postJson('/api/v1/account/keys/revoke', ['id' => $key->id])->assertNotFound();
        $this->assertFalse(ApiKey::whereKey($key->id)->active()->exists());
    }

    public function test_an_account_holds_at_most_ten_active_keys(): void
    {
        $account = $this->apiAccount('basic');
        $active = ApiKey::where('api_account_id', $account->id)->active()->count();
        for ($i = $active; $i < 10; $i++) {
            ApiKey::generateFor($account, 'key '.$i);
        }

        $this->asApiAccount($account)->postJson('/api/v1/account/keys', ['name' => 'one more'])
            ->assertUnprocessable()->assertJson(['error' => 'You already have the maximum of 10 active keys. Revoke one first.']);
    }

    public function test_activating_live_data_needs_a_key_and_expires_old_site_tokens(): void
    {
        $account = $this->apiAccount('fixtures');
        $api = DB::connection('heroesprofile_api');
        $api->table('api_tokens')->insert([
            'id' => 'legacy-token-1', 'user_id' => $account->id, 'name' => 'old', 'token' => str_repeat('a', 60),
            'metadata' => '[]', 'transient' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
        ApiKey::where('api_account_id', $account->id)->get()->each->revoke();

        $this->asApiAccount($account)->postJson('/api/v1/account/migrate')
            ->assertUnprocessable()->assertJson(['error' => 'Create an API key first. Activating live data expires your old key immediately.']);

        ApiKey::generateFor($account, 'new');
        $this->postJson('/api/v1/account/migrate')->assertOk()->assertJson(['migrated' => true]);
        $this->postJson('/api/v1/account/migrate')->assertStatus(409);

        $this->assertNotNull($api->table('api_tokens')->where('id', 'legacy-token-1')->value('expires_at'));
    }

    public function test_test_mode_can_be_left_only_with_a_plan(): void
    {
        $this->asApiAccount('fixtures')->postJson('/api/v1/account/test-mode', ['test_mode' => false])->assertUnprocessable();

        $this->asApiAccount('developer')->postJson('/api/v1/account/test-mode', ['test_mode' => true])
            ->assertOk()->assertJson(['test_mode' => true, 'receives_test_data' => true]);
        $this->postJson('/api/v1/account/test-mode', ['test_mode' => false])
            ->assertOk()->assertJson(['test_mode' => false, 'receives_test_data' => false]);
    }

    public function test_admin_mode_is_only_for_admins(): void
    {
        $this->asApiAccount('developer')->postJson('/api/v1/account/admin-mode', ['admin_mode' => true])->assertForbidden();

        $this->asApiAccount('admin')->postJson('/api/v1/account/admin-mode', ['admin_mode' => false])
            ->assertOk()->assertJson(['admin_mode' => false]);
        $this->assertFalse((bool) $this->apiAccount('admin')->admin_mode);
    }

    public function test_verification_mail_is_resent_until_verified(): void
    {
        $account = $this->apiAccount('basic');
        $account->forceFill(['email_verified_at' => null])->save();

        $this->asApiAccount($account)->postJson('/api/v1/account/email/resend')->assertExactJson(['sent' => true]);
        Notification::assertSentTo($account, VerifyApiEmail::class);

        $account->forceFill(['email_verified_at' => now()])->save();
        $this->postJson('/api/v1/account/email/resend')->assertStatus(409);
    }

    public function test_a_warning_is_acknowledged_once_read(): void
    {
        $account = $this->apiAccount('basic');
        $warning = app(AccountEnforcementService::class)->warn($account, '<p>Attribution is missing.</p>', null, null, null);
        Notification::assertSentTo($account, AccountWarned::class);

        $this->asApiAccount($account)->postJson('/api/v1/account/warning/acknowledge')->assertExactJson(['acknowledged' => true]);

        $this->assertNotNull($warning->fresh()->acknowledged_at);
        $this->assertNull($account->fresh()->unacknowledgedWarning());
    }

    public function test_account_writes_need_a_portal_sign_in(): void
    {
        $this->postJson('/api/v1/account/name', ['name' => 'x'])->assertRedirect('/Api/Login');
    }
}
