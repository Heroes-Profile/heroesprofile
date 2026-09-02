<?php

namespace App\Notifications\Api;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * Notice that something needs fixing. Access is untouched — this is the rung that
 * exists so a suspension is never the first thing a customer hears from us.
 */
class AccountWarned extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $reason,
        private readonly ?Carbon $respondBy = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Action needed on your Heroes Profile API account')
            ->greeting('Something needs your attention')
            ->line('Your API access is working normally and nothing has been restricted. We do need you to put something right:')
            ->line($this->reason);

        if ($this->respondBy !== null) {
            $message->line('Please sort this out by '.$this->respondBy->toFormattedDateString().'.');
        }

        return $message
            ->action('View your account', url('/Api/Account'))
            ->line('If you think this is a mistake, or you are not sure what we are asking for, reply to this email or write to zemill@heroesprofile.com and we will work it out.');
    }
}
