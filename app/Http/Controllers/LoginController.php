<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;

class LoginController extends Controller
{
  public function redirectToProvider()
  {
    //return Socialite::driver('github')->redirect();
    return Socialite::with('battlenet')->redirect();

  }

  /**
  * Obtain the user information from GitHub.
  *
  * @return \Illuminate\Http\Response
  */
  public function handleProviderCallback()
  {
    /*
    $user = Socialite::driver('github')->user();
    // $user->token;
    */
    /*
    $user = Socialite::driver('battlenet')->user();
    $accessTokenResponseBody = $user->accessTokenResponseBody;
    */
  }
}
