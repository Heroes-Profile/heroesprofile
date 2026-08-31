<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class ApiUsage extends Model
{
    /** Rolling window length, matching the old site's weekly reset. */
    public const WINDOW_DAYS = 7;

    protected $connection = 'heroesprofile_api';

    protected $table = 'api_usage';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'api_account_id',
        'endpoint',
        'calls',
        'egress_bytes',
        'window_started_at',
    ];

    protected $casts = [
        'calls' => 'integer',
        'egress_bytes' => 'integer',
        'window_started_at' => 'datetime',
    ];

    public function windowHasExpired(): bool
    {
        return $this->window_started_at === null
            || $this->window_started_at->lte(now()->subDays(self::WINDOW_DAYS));
    }

    /**
     * The table is keyed on (api_account_id, endpoint). Eloquent has no composite
     * key support, so left alone every save and delete targets `where id is null`.
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('api_account_id', $this->getOriginal('api_account_id', $this->api_account_id))
            ->where('endpoint', $this->getOriginal('endpoint', $this->endpoint));
    }
}
