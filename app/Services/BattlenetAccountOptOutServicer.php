<?php

namespace App;

use Laravel\Socialite\Contracts\User as BattlenetUser;

class BattlenetAccountOptOutServicer
{
    public function updateOptOut(BattlenetUser $battlenetUser)
    {
      $battlenetUser = json_decode(json_encode($battlenetUser),true);
      //print_r(json_encode($battlenetUser, true));

        $battletag_optout = battletag::where('battletag', 'mewnfare#11709'/*$battlenetUser["user"]["battletag"]*/)
                   ->get();
        $battletag_optout = json_decode(json_encode($battlenetUser),true);
        $battletagUsers = array();
        for($i = 0; $i < count($battletag_optout); $i++){
          $battletagUsers[$i] = $battletag_optout[$i];
        }


        print_r($battletagUsers);
        /*
        $blizzID_region_optOut = battletag::where('blizz_id', $battletag_optout[0]["blizz_id"])
                   ->where('region', $battletag_optout[0]["region"])
                   ->get();

        $battletag_optout->opt_out = 1;
        $battletag_optout->save();
        */
        return $battletag_optout;
    }
}
