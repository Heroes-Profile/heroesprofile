<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;


use App\Models\PatreonAccount;
use App\Models\BattlenetAccount;


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


        if($this->getUserDataCheckIfSubscribed($user->token)){
            $battlenetAccount->patreon = 1;
            $battlenetAccount->save();
        }

        return redirect('/Profile');
    }

    private function getUserDataCheckIfSubscribed($accessToken)
    {
        $client = new Client();
        $response = $client->get("https://www.patreon.com/api/oauth2/v2/identity", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
            ],
            'query' => [
                'include' => 'memberships.campaign',
                'fields[member]' => 'patron_status'
            ]
        ]);

        return $this->checkActivePatron(json_decode($response->getBody(), true), env('PATREON_CAMPAIGN_ID'));
    }

    private function checkActivePatron($api_return, $campaign_id) {
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

    //Code for determing all memberships.  Need to create backend to iterate through these and update users
    /*
    public function getCampaignData($accessToken)
    {
            $accessToken = "qfPOtcf8DyZofzrpMZ7su6-N1YMjpoQb2SfwgIbyp7c";

         $campaign_id = "2353115";  // your campaign ID

        $client = new Client();
        $response = $client->get("https://www.patreon.com/api/oauth2/v2/campaigns/{$campaign_id}/members", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
            ],
            'query' => [
                'include' => 'user',
                'fields[member]' => 'lifetime_support_cents,patron_status,pledge_relationship_start,currently_entitled_amount_cents',
                'fields[user]' => 'social_connections'
            ]
        ]);

        $membershipData = json_decode($response->getBody(), true);

        return $membershipData;
    }
    */
}
