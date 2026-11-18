<?php

namespace Tests\Feature\Characterization;

use App\Models\Api\ApiAccount;
use App\Models\BattlenetAccount;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

/** Pages and calls that need a signed-in Battle.net user or API portal account. */
class AuthenticatedPagesTest extends FeatureTestCase
{
    use AssertsSnapshots;
    use SeedFixtures;

    private function battlenetUser(): BattlenetAccount
    {
        // A fixed id: a rolled-back insert still moves AUTO_INCREMENT, so a generated one differs every run
        return BattlenetAccount::forceCreate([
            'battlenet_accounts_id' => 900001,
            'battlenet_id' => 123456789,
            'battletag' => self::PLAYER_BATTLETAG.'#194',
            'blizz_id' => self::PLAYER_BLIZZ_ID,
            'region' => self::PLAYER_REGION,
            'battlenet_access_token' => 'test',
        ]);
    }

    public static function battlenetPages(): array
    {
        return collect([
            'Profile/Settings', '/', 'Global/Hero', 'Analytics', 'Poll/does-not-exist',
            'Player/'.self::PLAYER_BATTLETAG.'/'.self::PLAYER_BLIZZ_ID.'/'.self::PLAYER_REGION,
        ])->mapWithKeys(fn ($uri) => [$uri => [$uri]])->all();
    }

    #[DataProvider('battlenetPages')]
    public function test_battlenet_page(string $uri): void
    {
        $response = $this->actingAs($this->battlenetUser())->get('/'.ltrim($uri, '/'));

        $this->assertResponseMatchesSnapshot('battlenet page '.$uri, $response);
    }

    public function test_saving_profile_settings(): void
    {
        $user = $this->battlenetUser();

        $response = $this->actingAs($user)->postJson('/api/v1/profile/save/settings', [
            'darkmode' => 1,
            'playerhistorytable' => 1,
            'talentbuilderstyle' => 'vertical',
        ]);

        $this->assertResponseMatchesSnapshot('battlenet post profile settings', $response);
        $this->assertMatchesSnapshot('battlenet profile settings saved', $user->userSettings()->orderBy('setting')->get(['setting', 'value'])->toArray());
    }

    /** Signed in to the API portal only; the site's own (Battle.net) guard stays the default, as in a real request. */
    private function asApiAccount(ApiAccount $account): static
    {
        $this->keepsSignedInUser = true;
        $this->app['auth']->guard('api_web')->setUser($account);

        return $this;
    }

    public static function apiPortalPages(): array
    {
        return collect([
            'Api/Account', 'Api/Account/Billing', 'Api/Docs', 'Api/Admin', 'Api',
        ])->mapWithKeys(fn ($uri) => [$uri => [$uri]])->all();
    }

    #[DataProvider('apiPortalPages')]
    public function test_api_portal_page_as_developer(string $uri): void
    {
        $account = ApiAccount::where('email', 'developer@heroesprofile.test')->firstOrFail();

        $response = $this->asApiAccount($account)->get('/'.$uri);

        $this->assertResponseMatchesSnapshot('api portal developer '.$uri, $response);
    }

    #[DataProvider('apiPortalPages')]
    public function test_api_portal_page_as_admin(string $uri): void
    {
        $account = ApiAccount::where('email', 'admin@heroesprofile.test')->firstOrFail();

        $response = $this->asApiAccount($account)->get('/'.$uri);

        $this->assertResponseMatchesSnapshot('api portal admin '.$uri, $response);
    }

    public function test_api_admin_metrics_and_usage(): void
    {
        $account = ApiAccount::where('email', 'admin@heroesprofile.test')->firstOrFail();

        foreach (['metrics', 'usage', 'activity', 'approvals/pending', 'polls', 'twitch'] as $path) {
            $response = $this->asApiAccount($account)->getJson('/api/v1/admin/'.$path);
            $this->assertResponseMatchesSnapshot('api admin '.$path, $response);
        }
    }
}
