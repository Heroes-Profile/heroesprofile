<?php

namespace App\Notifications\Api;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * Confirms a cancellation. Stripe sends nothing for this, and cancelling stays on the
 * old site for the whole transition, so this is the only acknowledgement a customer
 * gets either way.
 */
class SubscriptionCancelled extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ?string $planName,
        private readonly ?Carbon $endsAt,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $plan = $this->planName ?? 'API';

        $message = (new MailMessage)
            ->subject('Your Heroes Profile API subscription is cancelled')
            ->greeting('Subscription cancelled');

        if ($this->endsAt !== null && $this->endsAt->isFuture()) {
            // They paid through the end of the period, so say so plainly rather than
            // leaving them to guess whether their key died the moment they clicked.
            $message->line("Your {$plan} plan has been cancelled and will not renew.")
                ->line('Your API key keeps working until '.$this->endsAt->toFormattedDateString().'.');
        } else {
            $message->line("Your {$plan} plan has been cancelled and your API key is no longer active.");
        }

        return $message
            ->action('View billing', url('/Api/Account/Billing'))
            ->line('Changed your mind? You can resubscribe at any time from the billing page.');
    }
}
