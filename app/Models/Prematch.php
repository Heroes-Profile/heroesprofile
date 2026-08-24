<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prematch extends Model
{
    protected $table = 'prematch';

    protected $primaryKey = 'prematch_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    protected $fillable = [
        'prematch_replayID', 'game_type', 'game_map', 'team',
        'battletag', 'blizz_id', 'region', 'hero',
    ];
}
