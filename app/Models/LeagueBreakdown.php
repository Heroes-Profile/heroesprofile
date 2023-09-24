<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeagueBreakdown extends Model
{
    protected $table = 'league_breakdowns';
    protected $primaryKey = 'league_breakdowns_id';
    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
