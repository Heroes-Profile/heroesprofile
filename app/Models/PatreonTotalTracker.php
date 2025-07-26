<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatreonTotalTracker extends Model
{
    protected $table = 'patreon_total_tracker';

    protected $primaryKey = 'patreon_total_tracker_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
