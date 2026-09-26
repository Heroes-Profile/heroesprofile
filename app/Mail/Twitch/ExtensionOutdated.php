<?php

namespace App\Mail\Twitch;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * A live game used heroes or talents the released Twitch extension does not have,
 * so viewers are seeing "not available" for them until the next release.
 */
class ExtensionOutdated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $heroes  "Name (id)"
     * @param  array<int, string>  $talents  "Hero: Title (id)"
     */
    public function __construct(
        public string $channel,
        public ?string $releasedVersion,
        public array $heroes,
        public array $talents,
    ) {}

    public function build()
    {
        return $this->subject('Twitch extension is out of date')
            ->markdown('emails.twitch.extension-outdated');
    }
}
