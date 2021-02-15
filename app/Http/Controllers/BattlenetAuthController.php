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

    $clientId = env('BATTLENET_KEY', false);
    $clientSecret = env('BATTLENET_SECRET', false);
    $redirectUrl = env('BATTLENET_REDIRECT_URI', false);
    $additionalProviderConfig = ['region' => Session::get('battlenet_region')];
    $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl, $additionalProviderConfig);
    return Socialite::with('battlenet')->setConfig($config)->redirect();
  }

  /**
  * Obtain the user information from Battlenet.
  *
  * @return \Illuminate\Http\RedirectResponse
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

    $clientId = env('BATTLENET_KEY', false);
    $clientSecret = env('BATTLENET_SECRET', false);
    $redirectUrl = env('BATTLENET_REDIRECT_URI', false);
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
