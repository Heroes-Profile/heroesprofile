<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonDate extends Model
{
    protected $table = 'season_dates';

    protected $primaryKey = 'id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
