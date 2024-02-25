<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BattlenetAccount;
use App\Models\PatreonAccount;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class PatreonController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('patreon')
            ->scopes(['identity', 'identity[email]', 'campaigns'])
            ->redirect();
    }

    public function handleProviderCallback()
    {

      try {
        $user = Socialite::driver('patreon')->user();
        $patreonData = [
            'patreon_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'access_token' => $user->token,
            'remember_token' => $user->refreshToken,
            'expires_in' => $user->expiresIn,
        ];
        $currentBattlenetId = Auth::id();

        $patreonAccount = PatreonAccount::updateOrCreate(
            ['email' => $user->email],
            array_merge($patreonData, ['battlenet_accounts_id' => $currentBattlenetId])
        );

        $battlenetAccount = BattlenetAccount::find($currentBattlenetId);
        $data = $battlenetAccount->patreonAccount;

        if ($this->getUserDataCheckIfSubscribed($user->token)) {
            $battlenetAccount->patreon = 1;
            $battlenetAccount->save();
        }
        return redirect('/Profile/Settings');

      } catch (InvalidStateException $e) {
          // Log the exception for debugging (optional)
          Log::error('InvalidStateException in PatreonController: '.$e->getMessage());

          // Redirect the user to a custom login failed page
          return redirect('/Authenticate/Patreon/Failed');
      }
    }

    public function handleProviderCallbackFailed(Request $request)
    {
        return view('Patreon.authenticationFailed')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            ]);
    }

    private function getUserDataCheckIfSubscribed($accessToken)
    {
        $client = new Client();
        $response = $client->get('https://www.patreon.com/api/oauth2/v2/identity', [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
            ],
            'query' => [
                'include' => 'memberships.campaign',
                'fields[member]' => 'patron_status',
            ],
        ]);

        return $this->checkActivePatron(json_decode($response->getBody(), true), env('PATREON_CAMPAIGN_ID'));
    }

    private function checkActivePatron($api_return, $campaign_id)
    {
        $memberships = $api_return['included'] ?? [];

        foreach ($memberships as $membership) {
            if (
                isset($membership['relationships']['campaign']['data']['id']) &&
                $membership['relationships']['campaign']['data']['id'] == $campaign_id &&
                isset($membership['attributes']['patron_status']) &&
                $membership['attributes']['patron_status'] === 'active_patron'
            ) {
                return true;
            }
        }

        return false;
    }
}
