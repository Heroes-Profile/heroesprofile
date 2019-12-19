<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use Session;
use Cache;

class LoginController extends Controller
{
  public function redirectToProvider()
  {
    //Move this set of code to button press login (maybe, we'll see)
    Session::put('battlenet_region', 'us', 60);

    $clientId = env('BATTLE.NET_KEY', false);
    $clientSecret = env('BATTLE.NET_SECRET', false);
    $redirectUrl = env('BATTLE.NET_REDIRECT_URI', false);
    $additionalProviderConfig = ['region' => Cache::get('battlenet_region')];
    $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl, $additionalProviderConfig);
    return Socialite::with('battlenet')->setConfig($config)->redirect();
  }

  /**
  * Obtain the user information from Battlenet.
  *
  * @return \Illuminate\Http\Response
  */
  public function handleProviderCallback(\App\BattlenetAccountServicer $battlenetServicer)
  {
    $regionsToInt = array(
      "us" => "1",
      "eu" => "2",
      /*
      "KR" => "3",
      "UNK" => "4",
      "CN" => "5"
      */
    );


    try {
      $battlenet_user = Socialite::driver('battlenet')->user();
      $battlenet_user->user['region'] = $regionsToInt[Session::get('battlenet_region')];

    } catch (\Exception $e) {
      //return redirect('/login');
      return redirect('/login/failure');
    }

    $authUser = $battlenetServicer->findOrCreate(
        $battlenet_user
    );
    auth()->login($authUser, true);
    return redirect()->to('/');

  }
}
