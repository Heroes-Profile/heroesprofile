<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderAlert extends Model
{
    protected $table = 'header_alert';

    protected $primaryKey = 'header_alert_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
