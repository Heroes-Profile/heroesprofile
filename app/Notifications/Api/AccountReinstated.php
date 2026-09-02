<?php

namespace App\Notifications\Api;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Access restored. A reinstated termination is the awkward case: the keys work
 * again, but Stripe already ended the subscription and only the customer can start
 * a new one — so that is said rather than left to be discovered.
 */
class AccountReinstated extends Notification
{
    use Queueable;

    public function __construct(private readonly bool $wasTerminated = false) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your Heroes Profile API access has been restored')
            ->greeting('You are back')
            ->line('Your API keys work again. The same keys as before — nothing needs regenerating on your side.');

        if ($this->wasTerminated) {
            $message->line('One thing we could not undo: your subscription was cancelled when the account was closed, and we cannot restart it for you. Subscribe again from the billing page whenever you are ready.')
                ->action('Billing', url('/Api/Account/Billing'));
        } else {
            $message->action('View your account', url('/Api/Account'));
        }

        return $message->line('Thanks for putting it right.');
    }
}
