<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
    $clientId = env('BATTLE.NET_KEY', false);
    $clientSecret = env('BATTLE.NET_SECRET', false);
    $redirectUrl = env('BATTLE.NET_REDIRECT_URI', false);
    $additionalProviderConfig = ['region' => Session::get('battlenet_region')];
    $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl, $additionalProviderConfig);

    $user = Socialite::driver('battlenet')->setConfig($config)->user();

    $battlenet_user = \App\Models\BattlenetAccount::firstOrCreate(
      ['battlenet_id' => $user->id],
      ['battletag' => $user->nickname],
      ['region' => '1'],
      ['battlenet_access_token' => $user->accessTokenResponseBody["access_token"]],
      ['remember_token' => $user->token]
    );
    auth()->login($battlenet_user, true);

    
  }
}
