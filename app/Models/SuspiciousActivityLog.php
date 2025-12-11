<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuspiciousActivityLog extends Model
{
    protected $table = 'suspicious_activity_log';

    protected $primaryKey = 'suspicious_activity_log_id';

    protected $connection = 'heroesprofile_logs';

    public $timestamps = false;

    protected $fillable = ['ip', 'user_agent', 'path', 'reason'];
}
