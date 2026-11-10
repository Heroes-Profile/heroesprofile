<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroesDataWeapon extends Model
{
    protected $table = 'heroes_data_weapons';

    protected $primaryKey = 'id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
