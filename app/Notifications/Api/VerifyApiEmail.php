<?php

namespace App\Notifications\Api;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Laravel's stock VerifyEmail signs a link to `verification.verify`. That route name
 * belongs to the main site's `web` guard and does not exist here, so the portal needs
 * its own — same message, different destination.
 */
class VerifyApiEmail extends VerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'api.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
