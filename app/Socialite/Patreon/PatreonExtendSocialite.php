<?php

namespace App\Socialite\Patreon;

use SocialiteProviders\Manager\SocialiteWasCalled;

class PatreonExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('patreon', Provider::class);
    }
}
