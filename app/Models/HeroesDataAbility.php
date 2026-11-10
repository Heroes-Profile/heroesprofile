<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroesDataAbility extends Model
{
    protected $table = 'heroes_data_abilities';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
