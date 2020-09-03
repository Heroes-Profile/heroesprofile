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
    $user = Socialite::driver('battlenet')->userFromToken($request["code"]);
    print_r(json_encode($user, true));

    /*
    $response = Http::post('http://test.com/users', [
        'name' => 'Steve',
        'role' => 'Network Administrator',
    ]);
    */
  }
}
