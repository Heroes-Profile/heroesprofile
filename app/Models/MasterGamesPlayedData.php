<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterGamesPlayedData extends Model
{
    protected $table = 'master_games_played_data';

    protected $primaryKey = 'master_games_played_data_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
