<?php

namespace App\Mail\Api;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Admin copy of a subscription change. The old API site sent one of these on every
 * cancellation; Stripe has no per-event equivalent, so this stays app-side.
 */
class SubscriptionActivity extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $event,
        public string $userName,
        public string $userEmail,
        public ?string $plan,
        public ?string $endsAt,
    ) {}

    public function build()
    {
        return $this->subject($this->event.': '.$this->userEmail)
            ->markdown('emails.api.subscription-activity');
    }
}
