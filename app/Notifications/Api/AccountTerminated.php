<?php

namespace App\Notifications\Api;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Access withdrawn permanently. Terms §9: the licence ends and no refund is due,
 * but the recurring charge stops — which this says outright so they do not have to
 * ask, or ask their bank.
 */
class AccountTerminated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $reason,
        private readonly bool $subscriptionCancelled,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your Heroes Profile API access has been closed')
            ->greeting('Your API access has been closed')
            ->line('Your API keys have stopped working and your licence to use Heroes Profile data has ended. Here is why:')
            ->line($this->reason);

        if ($this->subscriptionCancelled) {
            $message->line('Your subscription has been cancelled, so you will not be charged again. As set out in section 9 of the terms, the current period is not refunded.');
        } else {
            $message->line('If you hold a subscription with us, contact us and we will make sure it is not charged again.');
        }

        return $message
            ->line('Section 3 of the terms requires you to stop using data you retrieved from us and delete what you have cached.')
            ->line('If you believe this is wrong, write to zemill@heroesprofile.com. You can still sign in to read this.');
    }
}
