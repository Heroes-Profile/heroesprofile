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
        return StandingMail::make(
            subject: 'Action needed on your Heroes Profile API account',
            headline: 'Something needs your attention',
            intro: ['Your API access is working normally and nothing has been restricted. We do need you to put something right:'],
            reason: $this->reason,
            outro: $this->respondBy === null ? [] : ['Please sort this out by '.$this->respondBy->toFormattedDateString().'.'],
            button: ['text' => 'View your account', 'url' => url('/Api/Account')],
            closing: 'If you think this is a mistake, or you are not sure what we are asking for, reply to this email and we will work it out.',
        );
    }
}
