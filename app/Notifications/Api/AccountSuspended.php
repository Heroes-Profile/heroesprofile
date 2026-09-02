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
        return (new MailMessage)
            ->subject('Your Heroes Profile API access has been suspended')
            ->greeting('Your API access is suspended')
            ->line('Your API keys have stopped working. Here is why:')
            ->line($this->reason)
            ->line('This is a suspension, not a closure. Your subscription is still running and your account, keys and usage history are all intact — access comes straight back once this is resolved.')
            ->action('View your account', url('/Api/Account'))
            ->line('You can still sign in to read this and reply. Write to zemill@heroesprofile.com and we will sort it out.');
    }
}
