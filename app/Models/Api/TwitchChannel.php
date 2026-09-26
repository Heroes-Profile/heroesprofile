<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A Twitch channel using the extension. See the migration for why rows are kept
 * after unlinking.
 */
class TwitchChannel extends Model
{
    /** Prefix on uploader keys, so a leaked one is recognisable in a paste. */
    public const KEY_PREFIX = 'hptw_';

    protected $connection = 'heroesprofile_api';

    protected $table = 'twitch_channels';

    protected $fillable = [
        'user_id',
        'twitch_user_id',
        'twitch_login',
        'twitch_display_name',
    ];

    protected $hidden = [
        'uploader_key_hash',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'blizz_id' => 'integer',
        'region' => 'integer',
        'delay_seconds' => 'integer',
        'show_stats' => 'boolean',
        'listing_opt_in' => 'boolean',
        'listing_terms_version' => 'integer',
        'uploader_last_seen_at' => 'datetime',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'comped_until' => 'datetime',
        'suspended_at' => 'datetime',
        'listing_terms_accepted_at' => 'datetime',
        'listing_hidden_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(ApiAccount::class, 'user_id', 'id');
    }

    public static function hashKey(string $plain): string
    {
        return hash('sha256', $plain);
    }

    public static function findByUploaderKey(string $plain): ?self
    {
        if (! str_starts_with($plain, self::KEY_PREFIX)) {
            return null;
        }

        return static::where('uploader_key_hash', static::hashKey($plain))->first();
    }

    /**
     * Issues a new uploader key and returns the plaintext. Only the hash is kept,
     * so the previous key stops working at once and this one is shown only here.
     */
    public function rotateUploaderKey(): string
    {
        $plain = self::KEY_PREFIX.Str::random(48);

        $this->forceFill([
            'uploader_key_hash' => static::hashKey($plain),
            'uploader_key_last4' => substr($plain, -4),
        ])->save();

        return $plain;
    }

    public function hasPlayerLinked(): bool
    {
        return $this->blizz_id !== null && $this->region !== null;
    }

    /** Opted in, on the current code of conduct, and not hidden by us. */
    public function isListable(): bool
    {
        return $this->listing_opt_in
            && $this->listing_terms_version === (int) config('twitch.listing_terms_version')
            && $this->listing_hidden_at === null
            && $this->suspended_at === null
            && $this->user_id !== null;
    }

    /** Starts the free month the first time live data arrives. Once per channel. */
    public function startTrialIfUnstarted(): void
    {
        if ($this->trial_started_at !== null) {
            return;
        }

        $this->forceFill([
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays((int) config('twitch.trial_days')),
        ])->save();
    }
}
