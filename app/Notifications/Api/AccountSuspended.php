<?php

namespace App\Notifications\Api;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Access withdrawn, reversibly. Says plainly that billing continues, because
 * finding that out from a statement instead of from us is how a suspension turns
 * into a chargeback.
 */
class AccountSuspended extends Notification
{
    use Queueable;

    public function __construct(private readonly string $reason) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return StandingMail::make(
            subject: 'Your Heroes Profile API access has been suspended',
            headline: 'Your API access is suspended',
            intro: ['Your API keys have stopped working. Here is why:'],
            reason: $this->reason,
            outro: ['This is a suspension, not a closure. Your subscription is still running and your account, keys and usage history are all intact — access comes straight back once this is resolved.'],
            button: ['text' => 'View your account', 'url' => url('/Api/Account')],
            closing: 'You can still sign in to read this. Reply to this email and we will sort it out.',
        );
    }
}
