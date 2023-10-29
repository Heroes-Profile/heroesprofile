<?php

namespace App\Models\HeroesInternational;

use Illuminate\Database\Eloquent\Model;

class HeroesInternationalNationsCupTeam extends Model
{
    protected $table = 'teams';

    protected $primaryKey = 'team_id';

    protected $connection = 'heroesprofile_hi_nc';

    public $timestamps = false;
}
