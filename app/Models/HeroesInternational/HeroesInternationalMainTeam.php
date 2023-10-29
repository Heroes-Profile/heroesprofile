<?php

namespace App\Models\HeroesInternational;

use Illuminate\Database\Eloquent\Model;

class HeroesInternationalMainTeam extends Model
{
    protected $table = 'teams';

    protected $primaryKey = 'team_id';

    protected $connection = 'heroesprofile_hi';

    public $timestamps = false;
}
