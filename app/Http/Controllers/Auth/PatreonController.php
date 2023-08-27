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
        return Socialite::driver('patreon')->redirect();
    }

    public function handleProviderCallback()
    {   
        /*
        $user = Socialite::driver('patreon')->user();
        $token = $user->token;

        $client = new Client();  //GuzzleHttp\Client
        $response = $client->request('GET', 'https://www.patreon.com/api/oauth2/v2/campaigns', [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        $campaignData = json_decode($response->getBody(), true);

        // Now $campaignData contains information about the campaigns the user is a member of.
        return response()->json($campaignData);
        */
        $user = Socialite::driver('patreon')->user();
        $patreonData = [
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


        //return response()->json($user);

        $battlenetAccount = BattlenetAccount::find($currentBattlenetId);
        $data = $battlenetAccount->patreonAccount;


        return $data;

    }
}
