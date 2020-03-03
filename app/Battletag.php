<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Battletag extends Model
{
    protected $fillable = ['blizz_id', 'battletag', 'region', 'account_level', 'patreon', 'opt_out', 'latest_game'];

    protected $primaryKey = 'player_id';

    protected $connection= 'mysql_dev';

}
