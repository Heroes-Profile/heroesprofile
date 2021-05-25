<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Socialite;
use Session;

class BattlenetAuthController extends Controller
{
  public function redirectToProviderOptOut(Request $request)
  {
    $clientId = env('BATTLENET_KEY_OPT', false);
    $clientSecret = env('BATTLENET_SECRET_OPT', false);
    $redirectUrl = env('BATTLENET_REDIRECT_URI_OPT', false);
    $additionalProviderConfig = ['region' => 'us'];
    $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl, $additionalProviderConfig);
    return Socialite::with('battlenet')->setConfig($config)->redirect();
  }

  /**
  * Obtain the user information from Battlenet.
  *
  * @return \Illuminate\Http\RedirectResponse
  */
  public function handleProviderCallbackOptOut(Request $request)
  {
    $clientId = env('BATTLENET_KEY_OPT', false);
    $clientSecret = env('BATTLENET_SECRET_OPT', false);
    $redirectUrl = env('BATTLENET_REDIRECT_URI_OPT', false);
    $additionalProviderConfig = ['region' => 'us'];
    $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl, $additionalProviderConfig);

    $user = Socialite::driver('battlenet')->setConfig($config)->user();



    $battletag_data = \App\Models\Battletag::where('battletag', $user->nickname)->get();
    foreach($battletag_data as $result){
      $result->opt_out = 1;
      $result->save();
    }
    return redirect('/Account/optout/success');
  }
}
