<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FriendFoeCache extends Model
{
    protected $connection = 'heroesprofile_cache';

    protected $table = 'friend_foe_cache';

    protected $fillable = [
        'blizz_id',
        'region',
        'type',
        'params_hash',
        'latest_replayID',
        'data',
    ];
}
