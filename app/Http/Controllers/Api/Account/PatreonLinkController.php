<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use App\Models\PatreonAccount;
use App\Services\Api\ApiKeyResolver;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

/**
 * Links an API account to a Patreon pledge.
 *
 * Separate from `Auth\PatreonController` because that one associates with a main-site
 * Battle.net user — a different identity that an API account cannot be found through.
 * Both write the same `patreon_accounts` row; only the column they link from differs.
 *
 * The redirect URI is overridden per-request rather than read from `services.patreon
 * .redirect`, so the main site's flow keeps its own callback untouched. **The URL below
 * has to be registered on the Patreon app as a second redirect URI**, or Patreon
 * refuses the handshake.
 */
class PatreonLinkController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('patreon')
            ->scopes(['identity', 'identity[email]', 'campaigns'])
            ->redirectUrl($this->callbackUrl())
            ->redirect();
    }

    public function handleProviderCallback(ApiKeyResolver $keys)
    {
        $account = Auth::guard('api_web')->user();

        try {
            $patreonUser = Socialite::driver('patreon')
                ->redirectUrl($this->callbackUrl())
                ->user();
        } catch (InvalidStateException $e) {
            Log::error('InvalidStateException linking Patreon to an API account: '.$e->getMessage());

            return redirect('/Api/Account')->withErrors(['patreon' => 'That Patreon sign-in did not complete. Please try again.']);
        }

        $record = $this->patreonRecord($patreonUser);

        // One pledge, one API account. Without this a single supporter could hand the
        // same Patreon to as many registrations as they liked.
        $takenBy = ApiAccount::where('patreon_accounts_id', $record->patreon_accounts_id)
            ->where('id', '!=', $account->id)
            ->exists();

        if ($takenBy) {
            return redirect('/Api/Account')->withErrors([
                'patreon' => 'That Patreon account is already linked to another API account.',
            ]);
        }

        $account->forceFill(['patreon_accounts_id' => $record->patreon_accounts_id])->save();

        // Entitlement is cached alongside the key, so the tier would not appear until
        // the entry aged out.
        $keys->forgetAccount($account->id);

        return redirect('/Api/Account')->with('status', 'Patreon linked.');
    }

    public function unlink(ApiKeyResolver $keys)
    {
        $account = Auth::guard('api_web')->user();

        $account->forceFill(['patreon_accounts_id' => null])->save();

        $keys->forgetAccount($account->id);

        return response()->json(['linked' => false]);
    }

    /**
     * Finds or creates the shared `patreon_accounts` row and refreshes the pledge.
     *
     * Matched on `patreon_id` first and email second: the main site creates these rows
     * keyed on email, and matching only on the id would fork a second row for someone
     * who linked there first.
     */
    private function patreonRecord($patreonUser): PatreonAccount
    {
        $record = PatreonAccount::where('patreon_id', $patreonUser->id)->first()
            ?? PatreonAccount::where('email', $patreonUser->email)->first()
            ?? new PatreonAccount;

        $record->fill([
            'patreon_id' => $patreonUser->id,
            'name' => $patreonUser->name,
            'email' => $patreonUser->email,
            'access_token' => $patreonUser->token,
            'remember_token' => $patreonUser->refreshToken,
            'expires_in' => $patreonUser->expiresIn,
        ]);

        // Written now rather than waiting for PatreonSubscriberChecker's next run,
        // which would leave someone who just linked with no tier until it fires.
        $cents = $this->pledgeCents($patreonUser->token);

        if ($cents !== null) {
            $record->currently_entitled_amount_cents = $cents;
        }

        $record->save();

        return $record;
    }

    /**
     * Current pledge to our campaign, in cents. Null when it cannot be read, which
     * leaves whatever the checker last wrote rather than overwriting it with a zero.
     */
    private function pledgeCents(string $accessToken): ?int
    {
        try {
            $response = (new Client)->get('https://www.patreon.com/api/oauth2/v2/identity', [
                'headers' => ['Authorization' => "Bearer {$accessToken}"],
                'query' => [
                    'include' => 'memberships.campaign',
                    'fields[member]' => 'patron_status,currently_entitled_amount_cents',
                ],
            ]);

            $payload = json_decode((string) $response->getBody(), true);
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        $campaignId = config('services.patreon.campaign_id');

        foreach ($payload['included'] ?? [] as $membership) {
            $belongs = ($membership['relationships']['campaign']['data']['id'] ?? null) == $campaignId;
            $active = ($membership['attributes']['patron_status'] ?? null) === 'active_patron';

            if ($belongs && $active) {
                return (int) ($membership['attributes']['currently_entitled_amount_cents'] ?? 0);
            }
        }

        // Reached our campaign and found no active pledge: a real zero, not a failure.
        return 0;
    }

    private function callbackUrl(): string
    {
        return url('/Api/Patreon/Callback');
    }
}
