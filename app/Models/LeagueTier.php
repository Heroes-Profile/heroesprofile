<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeagueTier extends Model
{
    protected $table = 'league_tiers';

    protected $primaryKey = 'tier_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
