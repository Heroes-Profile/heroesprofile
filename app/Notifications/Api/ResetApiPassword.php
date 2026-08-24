<?php

namespace App\Notifications\Api;

use Illuminate\Auth\Notifications\ResetPassword;

/**
 * Laravel's stock ResetPassword builds its link from `password.reset`. That route does
 * not exist here — `Auth::routes()` is never called, and the portal's route is named
 * `api.password.reset` — so the stock notification throws while rendering the mail.
 */
class ResetApiPassword extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        return url(route('api.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
