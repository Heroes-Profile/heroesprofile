<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatreonController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('patreon')->redirect();
    }

    public function handleProviderCallback()
    {
        $user = Socialite::driver('patreon')->user();
        // Do actions like logging in the user, or creating a new user entry
    }
}
