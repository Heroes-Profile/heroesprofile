<?php

namespace App\Notifications\Api;

use App\Support\StandingText;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The warn, suspend, close and reinstate emails, in the same layout as the
 * announcements sent by hand from Gmail rather than Laravel's stock template.
 */
class StandingMail
{
    /**
     * @param  array<int, string>  $intro
     * @param  array<int, string>  $outro
     * @param  array{text: string, url: string}|null  $button
     */
    public static function make(
        string $subject,
        string $headline,
        array $intro,
        ?string $reason,
        array $outro,
        ?array $button,
        string $closing,
    ): MailMessage {
        return (new MailMessage)
            ->subject($subject)
            ->view('emails.api.standing', [
                'headline' => $headline,
                'intro' => $intro,
                'reason' => StandingText::email($reason),
                'outro' => $outro,
                'button' => $button,
                'closing' => $closing,
            ]);
    }
}
