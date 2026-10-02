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
        return StandingMail::make(
            subject: 'Your Heroes Profile API access has been closed',
            headline: 'Your API access has been closed',
            intro: ['Your API keys have stopped working and your licence to use Heroes Profile data has ended. Here is why:'],
            reason: $this->reason,
            outro: [
                $this->subscriptionCancelled
                    ? 'Your subscription has been cancelled, so you will not be charged again. As set out in section 9 of the terms, the current period is not refunded.'
                    : 'If you hold a subscription with us, contact us and we will make sure it is not charged again.',
                'Section 3 of the terms requires you to stop using data you retrieved from us and delete what you have cached.',
            ],
            button: null,
            closing: 'If you believe this is wrong, reply to this email. You can still sign in to read this.',
        );
    }
}
