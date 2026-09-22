<?php

namespace App\Services\Twitch;

use Illuminate\Support\Facades\Auth;

/**
 * Whether the extension is shown to anyone but us.
 *
 * It cannot be added to a channel until Twitch has approved it, so the public page
 * and the portal's setup section are ours alone until then. The endpoints are not
 * gated: a channel already set up, ours included, keeps working.
 */
class TwitchAccess
{
    public static function visible(): bool
    {
        return (bool) config('twitch.public_enabled') || self::isAdmin();
    }

    private static function isAdmin(): bool
    {
        return (bool) (Auth::guard('api_web')->user()?->isAdmin() ?? false);
    }
}
