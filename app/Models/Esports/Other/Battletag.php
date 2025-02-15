<?php

namespace App\Models\Esports\Other;

use Illuminate\Database\Eloquent\Model;

class Battletag extends Model
{
    protected $table = 'battletags';

    protected $primaryKey = 'player_id';

    protected $connection = 'heroesprofile_esports_other';

    public $timestamps = false;
}
