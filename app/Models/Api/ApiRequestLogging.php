<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class ApiRequestLogging extends Model
{
    protected $table = 'api_request_logging';

    protected $primaryKey = 'api_request_logging_id';

    protected $connection = 'heroesprofile_logs';

    public $timestamps = false;

    protected $fillable = ['api_account_id', 'ip', 'method', 'page', 'parameters', 'status', 'user_agent'];
}
