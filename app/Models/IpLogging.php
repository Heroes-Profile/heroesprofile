<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpLogging extends Model
{
    protected $table = 'ip_logging';

    protected $primaryKey = 'ip_logging_id';

    protected $connection = 'heroesprofile_logs';

    public $timestamps = false;

    protected $fillable = [
        'ip',
        'page',
        'user_agent',
        'sec_ch_ua',
        'sec_ch_ua_mobile',
        'sec_ch_ua_platform',
        'sec_fetch_site',
        'sec_fetch_mode',
        'sec_fetch_dest',
        'accept_language',
        'accept_encoding',
        'referer',
    ];
}
