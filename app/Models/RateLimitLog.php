<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateLimitLog extends Model
{
    protected $table = 'rate_limit_log';

    protected $primaryKey = 'rate_limit_log_id';

    protected $connection = 'heroesprofile_logs';

    public $timestamps = false;

    protected $fillable = [
        'ip',
        'user_id',
        'http_method',
        'path',
        'query_string',
        'limiter',
        'replay_id',
        'is_old_replay',
        'user_agent',
        'referer',
        'retry_after',
        'date_time',
    ];

    protected $casts = [
        'is_old_replay' => 'boolean',
        'date_time' => 'datetime',
        'retry_after' => 'integer',
        'replay_id' => 'integer',
        'user_id' => 'integer',
    ];
}
