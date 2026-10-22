<?php

namespace App\Notifications\Api;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Laravel\Cashier\Cashier;

/**
 * Access withdrawn, reversibly. Says plainly whether billing continues, because
 * finding that out from a statement instead of from us is how a suspension turns
 * into a chargeback.
 */
class AccountSuspended extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $reason,
        private readonly bool $subscriptionCancelled = false,
        private readonly ?int $refundedCents = null,
    ) {}

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
            outro: $this->subscriptionCancelled ? [
                'This is a suspension, not a closure. Your account, keys and usage history are all intact.',
                $this->refundedCents > 0
                    ? 'Your subscription has been cancelled, so you will not be charged again, and the unused part of your current period — '.Cashier::formatAmount($this->refundedCents).' — has been refunded to your original payment method. Refunds usually take 5–10 business days to appear.'
                    : 'Your subscription has been cancelled, so you will not be charged again.',
            ] : ['This is a suspension, not a closure. Your subscription is still running and your account, keys and usage history are all intact — access comes straight back once this is resolved.'],
            button: ['text' => 'View your account', 'url' => url('/Api/Account')],
            closing: 'You can still sign in to read this. Reply to this email and we will sort it out.',
        );
    }
}
