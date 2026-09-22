<?php

namespace App\Socialite\Twitch;

use SocialiteProviders\Manager\SocialiteWasCalled;

class TwitchExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('twitch', Provider::class);
    }
}
