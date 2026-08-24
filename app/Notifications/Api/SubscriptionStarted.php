<?php

namespace App\Notifications\Api;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Covers both a new subscription and a plan swap. Stripe's receipt says what was
 * charged; neither receipt says which tier that bought or what it unlocks.
 */
class SubscriptionStarted extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ?string $planName,
        private readonly bool $changed = false,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $plan = $this->planName ?? 'API';

        $message = (new MailMessage)
            ->subject($this->changed
                ? 'Your Heroes Profile API plan has changed'
                : 'Your Heroes Profile API subscription is active');

        if ($this->changed) {
            $message->greeting('Plan changed')
                ->line("You are now on the {$plan} plan.");
        } else {
            $message->greeting('Subscription active')
                ->line("Thanks for subscribing. Your {$plan} plan is active and your API key is ready to use.");
        }

        return $message
            ->line('Your endpoint allowances update immediately.')
            ->action('View billing', url('/Api/Account/Billing'))
            ->line('Your invoices are listed on the billing page.');
    }
}
