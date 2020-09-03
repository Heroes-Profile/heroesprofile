<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use Session;

class BattlenetAuthController extends Controller
{
  public function redirectToProvider()
  {
    //Move this set of code to button press login (maybe, we'll see)
    Session::put('battlenet_region', 'us', 60);

    $clientId = env('BATTLE.NET_KEY', false);
    $clientSecret = env('BATTLE.NET_SECRET', false);
    $redirectUrl = env('BATTLE.NET_REDIRECT_URI', false);
    $additionalProviderConfig = ['region' => Session::get('battlenet_region')];
    $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl, $additionalProviderConfig);
    return Socialite::with('battlenet')->setConfig($config)->redirect();
  }

  /**
  * Obtain the user information from Battlenet.
  *
  * @return \Illuminate\Http\Response
  */
  public function handleProviderCallback(Request $request)
  {
    $user = Socialite::driver('battlenet')->user();
    $accessTokenResponseBody = $user->accessTokenResponseBody;
    print_r(json_encode($request, true));

    /*
    $battlenet_user = json_decode(json_encode($battlenet_user),true);
    */
    echo "success";
    //print_r($battlenet_user);
    /*
    $battlenet_accounts = \App\Models\battlenet_accounts::firstOrCreate(
      ['battlenet_id' => 'Flight 10'],
      ['battletag' => 'Flight 10'],
      ['region' => 'Flight 10'],
      ['battlenet_access_token' => 'Flight 10'],
      ['remember_token' => 'Flight 10'],
    );

    auth()->login($battlenet_accounts, true);
    return redirect()->to('/home');
    */
  }
}
