<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroesDataUnit extends Model
{
    protected $table = 'heroes_data_units';

    protected $primaryKey = 'id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
