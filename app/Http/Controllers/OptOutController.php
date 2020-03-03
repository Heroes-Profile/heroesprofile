<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;

class OptOutController extends Controller
{
  public function redirectToProvider(Request $request)
  {
    //Move this set of code to button press login (maybe, we'll see)
    //Session::put('battlenet_region', $request["region"], 60);

    $clientId = env('BATTLE.NET_KEY', false);
    $clientSecret = env('BATTLE.NET_SECRET', false);
    $redirectUrl = env('BATTLE.NET_REDIRECT_URI', false);
    $additionalProviderConfig = ['region' => $request["region"]];
    $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl, $additionalProviderConfig);
    return Socialite::with('battlenet')->setConfig($config)->redirect();
  }


  /**
  * Obtain the user information from Battlenet.
  *
  * @return \Illuminate\Http\Response
  */
  public function handleProviderCallback(\App\Services\BattlenetAccountOptOutServicer $battlenetOptOutServicer)
  {
    try {
      $battlenet_user = Socialite::driver('battlenet')->user();
      $battlenet_user->user['region'] = $regionsToInt[Session::get('battlenet_region')];

    } catch (\Exception $e) {
      //return redirect('/login');
      return redirect('/optOut/failure');
    }

    $authUser = $battlenetOptOutServicer->updateOptOut(
        $battlenet_user
    );

    return redirect()->to('/OptOut/Success');

  }

}
