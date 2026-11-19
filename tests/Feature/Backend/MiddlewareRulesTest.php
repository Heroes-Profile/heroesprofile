<?php

namespace Tests\Feature\Backend;

use App\Models\Api\ApiAccount;
use App\Models\BattlenetAccount;
use App\Services\WhitelistedIPsService;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Characterization\SeedFixtures;
use Tests\Feature\FeatureTestCase;
use Tests\Support\SignsInApiAccounts;

/**
 * The rules middleware enforces on its own: private and banned profiles, custom games, IP bans,
 * POST origins, rate limits, the API portal's gates, and the public API's key, quota and poison layers.
 */
class MiddlewareRulesTest extends FeatureTestCase
{
    use SeedFixtures;
    use SignsInApiAccounts;

    private const PROFILE = '/Player/'.self::PLAYER_BATTLETAG.'/'.self::PLAYER_BLIZZ_ID.'/'.self::PLAYER_REGION;

    protected function tearDown(): void
    {
        // A per-process memo; a whitelisted address would otherwise stay whitelisted for later tests
        (fn () => self::$checked = [])->bindTo(null, WhitelistedIPsService::class)();

        parent::tearDown();
    }

    private function account(int $id, int $blizzId, bool $private = false): BattlenetAccount
    {
        return BattlenetAccount::forceCreate([
            'battlenet_accounts_id' => $id, 'battlenet_id' => $id, 'battletag' => 'Viewer#'.$id,
            'blizz_id' => $blizzId, 'region' => self::PLAYER_REGION, 'private' => $private ? 1 : 0,
        ]);
    }

    private function playerData()
    {
        return $this->postJson('/api/v1/player/mmr', [
            'blizz_id' => self::PLAYER_BLIZZ_ID, 'region' => self::PLAYER_REGION, 'battletag' => self::PLAYER_BATTLETAG, 'game_type' => 'sl',
        ]);
    }

    // Private and banned profiles

    public function test_a_private_profile_is_hidden_from_others_but_not_its_owner(): void
    {
        $owner = $this->account(900040, self::PLAYER_BLIZZ_ID, private: true);
        $other = $this->account(900041, 123);

        $this->get(self::PROFILE)->assertRedirect('/');
        $this->playerData()->assertForbidden()->assertExactJson(['status' => 'private']);

        $this->actingAs($other)->get(self::PROFILE)->assertRedirect('/');

        $this->actingAs($owner)->get(self::PROFILE)->assertOk();
        $this->playerData()->assertOk();
    }

    public function test_a_banned_account_is_hidden_from_everyone_including_its_owner(): void
    {
        $owner = $this->account(900042, self::PLAYER_BLIZZ_ID);
        DB::connection('heroesprofile')->table('banned_accounts')->insert(['blizz_id' => self::PLAYER_BLIZZ_ID, 'region' => self::PLAYER_REGION]);

        $this->actingAs($owner)->get(self::PROFILE)->assertRedirect('/');
        $this->playerData()->assertForbidden();
    }

    public function test_the_private_check_reads_the_route_not_the_query_string(): void
    {
        $this->account(900043, self::PLAYER_BLIZZ_ID, private: true);

        $this->get(self::PROFILE.'?blizz_id=123&region=1')->assertRedirect('/');
    }

    // Custom games

    public function test_custom_games_are_withheld_unless_the_player_shows_them(): void
    {
        $customOnly = fn () => $this->postJson('/api/v1/player/mmr', [
            'blizz_id' => self::PLAYER_BLIZZ_ID, 'region' => self::PLAYER_REGION, 'battletag' => self::PLAYER_BATTLETAG, 'game_type' => 'cu',
        ]);

        $customOnly()->assertOk()->assertExactJson(['errors' => ['Custom games are not available.'], 'status' => 'failure to validate inputs']);

        // Mixed with other types, custom is dropped and the rest answered
        $this->postJson('/api/v1/player/mmr', [
            'blizz_id' => self::PLAYER_BLIZZ_ID, 'region' => self::PLAYER_REGION, 'battletag' => self::PLAYER_BATTLETAG, 'game_type' => 'sl,cu',
        ])->assertOk()->assertJsonMissing(['errors' => ['Custom games are not available.']]);
    }

    // Production-only rules

    private function asProduction(): void
    {
        config(['app.env' => 'production']);
    }

    public function test_a_banned_ip_is_refused_and_a_whitelisted_one_is_not(): void
    {
        $this->asProduction();
        $logs = DB::connection('heroesprofile_logs');
        $logs->table('banned_ips')->insert(['ip' => '203.0.113.7', 'reason' => 'Scraping', 'banned_date' => '2026-11-01 00:00:00']);
        $logs->table('banned_ips')->insert(['ip' => '203.0.113.8', 'reason' => 'Scraping', 'banned_date' => '2026-11-01 00:00:00']);
        $logs->table('whitelist_ips')->insert(['ip' => '203.0.113.8', 'reason' => 'Partner']);

        $this->get('/FAQ', ['CF-Connecting-IP' => '203.0.113.7'])->assertForbidden()->assertSee('Access denied. Your IP address has been banned.');
        $this->get('/FAQ', ['CF-Connecting-IP' => '203.0.113.8'])->assertOk();
        $this->get('/FAQ', ['CF-Connecting-IP' => '203.0.113.9'])->assertOk();
    }

    public function test_site_posts_need_the_sites_own_origin_in_production(): void
    {
        $this->asProduction();
        $search = fn (array $headers) => $this->postJson('/api/v1/battletag/search', ['userinput' => 'Zemill'], $headers + ['CF-Connecting-IP' => '203.0.113.20']);

        $search([])->assertForbidden()->assertExactJson(['message' => 'Forbidden.']);
        $search(['Origin' => 'https://evil.example'])->assertForbidden();
        $search(['Origin' => 'https://user@'.parse_url(config('app.url'), PHP_URL_HOST)])->assertForbidden();
        $this->assertNotSame(403, $search(['Origin' => rtrim(config('app.url'), '/')])->status());
        $this->assertNotSame(403, $search(['Referer' => rtrim(config('app.url'), '/').'/Player/Search'])->status());
    }

    public function test_old_replay_pages_have_their_own_tighter_limit_in_production(): void
    {
        $this->asProduction();

        for ($i = 0; $i < 15; $i++) {
            $this->assertNotSame(429, $this->get('/Match/Single/1', ['CF-Connecting-IP' => '203.0.113.30'])->status());
        }
        $this->get('/Match/Single/1', ['CF-Connecting-IP' => '203.0.113.30'])->assertTooManyRequests();

        // Another address has its own bucket
        $this->assertNotSame(429, $this->get('/Match/Single/1', ['CF-Connecting-IP' => '203.0.113.31'])->status());
    }

    public function test_site_pages_share_a_per_ip_limit_the_api_paths_skip(): void
    {
        for ($i = 0; $i < 40; $i++) {
            $this->get('/Twitch/Guidelines', ['CF-Connecting-IP' => '203.0.113.40'])->assertOk();
        }
        $this->get('/Twitch/Guidelines', ['CF-Connecting-IP' => '203.0.113.40'])->assertTooManyRequests();

        // API paths are limited elsewhere, not by this bucket
        $this->postJson('/api/v1/main/footer/data', [], ['CF-Connecting-IP' => '203.0.113.40'])->assertOk();
    }

    // API portal gates

    public function test_new_terms_hold_page_views_until_accepted(): void
    {
        $account = $this->apiAccount('basic');
        $account->forceFill(['terms_version_accepted' => '2020-01-01'])->save();
        $this->asApiAccount($account);

        $this->get('/Api/Account/Billing')->assertRedirect('/Api/Terms');
        $this->assertSame(url('/Api/Account/Billing'), session('terms_intended'));

        // Calls from the page aren't redirected
        $this->postJson('/api/v1/account/name', ['name' => 'Still works'])->assertOk();

        $this->post('/Api/Terms/Accept')->assertRedirect(url('/Api/Account/Billing'));
        $this->get('/Api/Account/Billing')->assertOk();
    }

    public function test_a_paying_account_without_project_details_is_sent_to_fill_them_in(): void
    {
        $account = $this->apiAccount('basic');
        $account->forceFill(['project_name' => null, 'project_description' => null])->save();
        $this->asApiAccount($account);

        // The account pages stay open so it can be filled in; anything else waits
        $this->get('/Api/Analytics/Battlenet/Link')->assertRedirect('/Api/Account#project');
        $this->get('/Api/Account')->assertOk();
        $this->get('/Api/Account/Billing')->assertOk();

        // Without a plan there is nothing to hold
        $free = $this->apiAccount('fixtures');
        $free->forceFill(['project_name' => null, 'project_description' => null])->save();
        $location = (string) $this->asApiAccount($free)->get('/Api/Analytics/Battlenet/Link')->headers->get('Location');
        $this->assertStringNotContainsString('#project', $location);
    }

    public function test_a_suspended_or_terminated_account_cannot_make_changes(): void
    {
        $account = $this->apiAccount('basic');
        $account->forceFill(['suspended_at' => now(), 'suspension_type' => 'suspension', 'suspension_reason' => 'x'])->save();
        $this->asApiAccount($account);

        $this->postJson('/api/v1/account/keys', ['name' => 'x'])->assertForbidden()
            ->assertExactJson(['error' => 'This account is suspended. See the notice on your account page.']);

        $account->forceFill(['suspension_type' => ApiAccount::TERMINATION])->save();
        $this->postJson('/api/v1/account/test-mode', ['test_mode' => true])->assertForbidden()
            ->assertExactJson(['error' => 'This account has been closed. See the notice on your account page.']);

        // Reading and dismissing still work
        $this->postJson('/api/v1/account/warning/acknowledge')->assertOk();
    }

    // Public API: keys, quota, poison

    private static function key(string $account): string
    {
        return str_pad($account.'_local_api_key_', 60, '0');
    }

    private function maps(array $headers = [], ?string $key = 'basic')
    {
        return $this->getJson('/api/external/v1/maps'.($key ? '?api_token='.self::key($key) : ''), $headers);
    }

    private function usage(string $slug, string $endpoint): ?object
    {
        return DB::connection('heroesprofile_api')->table('api_usage')
            ->where('api_account_id', $this->apiAccount($slug)->id)->where('endpoint', $endpoint)->first();
    }

    public function test_a_key_is_read_from_the_bearer_header_too(): void
    {
        $this->getJson('/api/external/v1/maps', ['Authorization' => 'Bearer '.self::key('basic')])->assertOk();
        $this->getJson('/api/external/v1/maps', ['Authorization' => 'Bearer '.str_repeat('y', 60)])->assertUnauthorized();
    }

    public function test_a_successful_call_is_charged_and_the_weekly_limit_is_enforced(): void
    {
        $this->maps()->assertOk()->assertHeader('X-HP-Quota-Limit', '70')->assertHeader('X-HP-Quota-Remaining', '69');
        $this->assertSame(1, (int) $this->usage('basic', 'maps')->calls);

        DB::connection('heroesprofile_api')->table('api_usage')->where('api_account_id', $this->apiAccount('basic')->id)
            ->where('endpoint', 'maps')->update(['calls' => 70]);

        $response = $this->maps()->assertStatus(429)->assertJsonPath('error.code', 'quota_exceeded')
            ->assertHeader('X-HP-Quota-Remaining', '0');
        $this->assertGreaterThan(0, (int) $response->headers->get('Retry-After'));
        $this->assertSame(70, (int) $this->usage('basic', 'maps')->calls, 'a refused call costs nothing');

        // A week on, the window starts again
        $this->travel(8)->days();
        $this->maps()->assertOk();
        $this->assertSame(1, (int) $this->usage('basic', 'maps')->calls);
    }

    public function test_an_endpoint_outside_the_plan_is_refused(): void
    {
        $endpoint = DB::connection('heroesprofile_api')->table('api_endpoints')->where('endpoint', 'maps')->value('endpoint_id');
        DB::connection('heroesprofile_api')->table('api_endpoint_quotas')->where('endpoint_id', $endpoint)->where('subscription_plan', 1)->update(['calls_per_week' => 0]);

        $this->maps()->assertForbidden()->assertJsonPath('error.code', 'endpoint_not_in_plan');
    }

    public function test_admins_and_fixture_accounts_are_not_metered(): void
    {
        $this->maps(key: 'admin')->assertOk();
        $this->maps(key: 'fixtures')->assertOk();

        $this->assertNull($this->usage('admin', 'maps'));
        $this->assertNull($this->usage('fixtures', 'maps'));
    }

    public function test_a_suspended_key_gets_its_own_error_before_anything_else(): void
    {
        $this->apiAccount('basic')->forceFill(['suspended_at' => now(), 'suspension_type' => 'suspension', 'suspension_reason' => 'x'])->save();

        $response = $this->maps()->assertForbidden();
        $this->assertNotSame('subscription_inactive', $response->json('error.code'));
        $this->assertNull($this->usage('basic', 'maps'));
    }

    public function test_unaccepted_terms_stop_the_key_once_the_grace_period_ends(): void
    {
        $this->apiAccount('basic')->forceFill(['terms_version_accepted' => '2020-01-01'])->save();

        config(['api.terms_enforce_from' => now()->addDay()->toDateString()]);
        $this->maps()->assertOk();

        config(['api.terms_enforce_from' => now()->subDay()->toDateString()]);
        $this->maps()->assertForbidden()->assertJsonPath('error.code', 'terms_not_accepted');
    }

    public function test_a_poisoned_account_gets_marked_global_stats_and_each_mark_is_logged(): void
    {
        $query = '/api/external/v1/heroes/stats?'.http_build_query([
            'timeframe_type' => 'minor', 'timeframe' => self::GAME_VERSION, 'game_type' => 'Storm League',
        ]);
        $clean = $this->getJson($query.'&api_token='.self::key('basic'))->assertOk()->json();

        $account = $this->apiAccount('developer');
        $account->forceFill(['poisoned_at' => now()])->save();
        $marked = $this->getJson($query.'&api_token='.self::key('developer'))->assertOk()->json();

        $this->assertNotSame($clean, $marked, 'some win rates are nudged');
        $logged = DB::connection('heroesprofile_api')->table('api_poison_log')->where('api_account_id', $account->id)->get();
        $this->assertNotEmpty($logged);
        $this->assertSame('heroes/stats', $logged[0]->endpoint);
        $this->assertNotEquals($logged[0]->real_value, $logged[0]->sent_value);
    }
}
