<?php

namespace App\Notifications\Api;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/** A question about how they use the API. Says up front that nothing is wrong. */
class AccountInfoRequested extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $message,
        private readonly ?Carbon $respondBy = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return StandingMail::make(
            subject: 'A question about your Heroes Profile API project',
            headline: 'We have a question about your project',
            intro: ['Your API access is working normally and nothing has been restricted. We would like to understand a little more about how you use the API:'],
            reason: $this->message,
            outro: $this->respondBy === null ? [] : ['Please reply by '.$this->respondBy->toFormattedDateString().'.'],
            button: null,
            closing: 'Just reply to this email with your answers.',
        );
    }
}
