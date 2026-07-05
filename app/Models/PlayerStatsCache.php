<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerStatsCache extends Model
{
    protected $connection = 'heroesprofile_cache';

    protected $table = 'player_stats_cache';

    protected $fillable = [
        'blizz_id',
        'region',
        'params_hash',
        'latest_replayID',
        'data',
    ];
}
