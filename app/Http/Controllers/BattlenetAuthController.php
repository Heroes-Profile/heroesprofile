<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BattlenetAccountServicer;
use Socialite;
use Session;
use Cache;

class BattlenetAuthController extends Controller
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
  public function handleProviderCallback(BattlenetAccountServicer $battlenetServicer)
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

  /**
  * Obtain the user information from Battlenet.
  *
  * @return \Illuminate\Http\Response
  */
  public function handleOptOutProviderCallback()
  {
      $regionsToInt = array(
        "us" => "1",
        "eu" => "2",
        "KR" => "3",
        "UNK" => "4",
        "CN" => "5"
      );
    try {
      $battlenet_user = Socialite::driver('battlenet')->user();
      $battlenet_user->user['region'] = $regionsToInt[Session::get('battlenet_region')];

      $battlenet_user = json_decode(json_encode($battlenet_user),true);
      $battletags = \App\Battletag::where('battletag', $battlenet_user["user"]["battletag"])
                    ->get();
      if(count($battletags) > 0){
        foreach($battletags as $battletag_key => $battletag_value){
          $blizzID_region = \App\Battletag::where('blizz_id', $battletag_value["blizz_id"])
                        ->where('region', $battletag_value["region"])
                        ->get();
          foreach($blizzID_region as $blizzIDRegion_key => $blizzIDRegion_value){
            $blizzIDRegion_value->opt_out = 1;
            $blizzIDRegion_value->save();
          }
        }
      }else{
        $battletag = new \App\Battletag;
        $battletag->battletag = $battlenet_user["user"]["battletag"];
        $battletag->region = $regionsToInt[Session::get('battlenet_region')];
        $battletag->opt_out = 1;
        $battletag->blizz_id = $battlenet_user["user"]["id"];
        $battletag->save();
      }

    } catch (\Exception $e) {
      return redirect('/optout/failure');
    }




    return redirect()->to('/optout/success');
  }


}
