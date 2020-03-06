<?php

namespace App\Services;

use Laravel\Socialite\Contracts\User as BattlenetUser;

class BattlenetAccountServicer
{
    public function findOrCreate(BattlenetUser $battlenetUser)
    {
      $battlenetUser = json_decode(json_encode($battlenetUser),true);
      //print_r(json_encode($battlenetUser, true));

        $user = \App\User::where('battlenet_id', $battlenetUser["user"]["id"])
                   ->first();

        if ($user) {
            return $user;
        } else {
          $user = \App\User::create([
              'battlenet_id' => $battlenetUser["user"]["id"],
              'battletag'  => $battlenetUser["user"]["battletag"],
              'region'  => $battlenetUser["user"]["region"],
              'battlenet_access_token'  => $battlenetUser["token"],
          ]);

          return $user;

        }
    }
}
