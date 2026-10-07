<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'compute_ms',
        'db_ms',
        'window_started_at',
    ];

    protected $casts = [
        'calls' => 'integer',
        'egress_bytes' => 'integer',
        'compute_ms' => 'integer',
        'db_ms' => 'integer',
        'window_started_at' => 'datetime',
    ];

    /** Adds time to an account's row for an endpoint, if it has one. */
    public static function addTime(int $accountId, string $endpoint, int $computeMs, int $dbMs): void
    {
        $computeMs = max(0, $computeMs);
        $dbMs = max(0, $dbMs);

        if ($computeMs === 0 && $dbMs === 0) {
            return;
        }

        static::query()
            ->where('api_account_id', $accountId)
            ->where('endpoint', $endpoint)
            ->update([
                'compute_ms' => DB::raw('compute_ms + '.$computeMs),
                'db_ms' => DB::raw('db_ms + '.$dbMs),
            ]);
    }

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
