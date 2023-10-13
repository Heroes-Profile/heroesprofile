<?php

namespace App\Models\NGS;

use Illuminate\Database\Eloquent\Model;

class Battletag extends Model
{
    protected $table = 'battletags';

    protected $primaryKey = 'player_id';

    protected $connection = 'heroesprofile_ngs';

    public $timestamps = false;
}
