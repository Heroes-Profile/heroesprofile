<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterGamesPlayedDataGroups extends Model
{
    protected $table = 'master_games_played_data_groups';

    protected $primaryKey = 'master_games_played_data_groups_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
