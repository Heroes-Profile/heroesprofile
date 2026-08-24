<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    /** Matches the length the old API site issued. */
    public const SECRET_LENGTH = 60;

    protected $connection = 'heroesprofile_api';

    protected $table = 'api_keys';

    protected $fillable = [
        'api_account_id',
        'name',
        'secret_hash',
    ];

    protected $hidden = [
        'secret_hash',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(ApiAccount::class, 'api_account_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Returns [$model, $plainTextKey]. The plaintext is never stored and cannot be
     * recovered — it is shown once at creation.
     */
    public static function generateFor(ApiAccount $account, string $name): array
    {
        $plain = Str::random(self::SECRET_LENGTH);

        $key = static::create([
            'api_account_id' => $account->id,
            'name' => $name,
            'secret_hash' => static::hash($plain),
        ]);

        return [$key, $plain];
    }

    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }
}
