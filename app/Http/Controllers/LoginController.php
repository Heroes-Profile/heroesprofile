<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;


class LoginController extends Controller
{
  public function redirectToProvider()
  {
    return Socialite::with('battlenet')->redirect();
  }

  /**
  * Obtain the user information from Battlenet.
  *
  * @return \Illuminate\Http\Response
  */
  public function handleProviderCallback(\App\BattlenetAccountServicer $battlenetServicer)
  {
    try {
      $user = Socialite::driver('battlenet')->user();
    } catch (\Exception $e) {
        return redirect('/login');
    }
    $authUser = $battlenetServicer->findOrCreate(
        $user
    );
    auth()->login($authUser, true);
    return redirect()->to('/');
  }
}
