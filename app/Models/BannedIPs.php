<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedIPs extends Model
{
    protected $table = 'banned_ips';

    protected $primaryKey = 'banned_ips_id';

    protected $connection = 'heroesprofile_logs';

    public $timestamps = false;

    protected $fillable = ['ip', 'reason', 'banned_at'];
}
