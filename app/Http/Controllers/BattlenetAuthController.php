<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Socialite;
use Session;

class BattlenetAuthController extends Controller
{
  public function redirectToProvider(Request $request)
  {
    Session::put('battlenet_region', $request->region);

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
    $region = 0;
    if(Session::get('battlenet_region') == 'us'){
      $region = 1;
    }else if(Session::get('battlenet_region') == 'eu'){
      $region = 2;
    }else if(Session::get('battlenet_region') == 'kr'){
      $region = 3;
    }else if(Session::get('battlenet_region') == 'cn'){
      $region = 5;
    }

    $clientId = env('BATTLE.NET_KEY', false);
    $clientSecret = env('BATTLE.NET_SECRET', false);
    $redirectUrl = env('BATTLE.NET_REDIRECT_URI', false);
    $additionalProviderConfig = ['region' => Session::get('battlenet_region')];
    $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl, $additionalProviderConfig);

    $user = Socialite::driver('battlenet')->setConfig($config)->user();
    $battlenet_user = \App\Models\BattlenetAccount::updateOrCreate(
      ['battlenet_id' => $user->id, 'battletag' => $user->nickname, 'region' => $region],
      ['battlenet_access_token' => $user->accessTokenResponseBody["access_token"], 'remember_token' => $user->token]
    );
    auth()->login($battlenet_user, true);
    return redirect('/Account');
  }
}
