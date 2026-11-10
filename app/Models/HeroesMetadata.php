<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroesMetadata extends Model
{
    protected $table = 'heroes_metadata';

    protected $primaryKey = 'hero_id';

    public $incrementing = false;

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
