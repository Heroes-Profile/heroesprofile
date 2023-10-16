<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    protected $table = 'heroes';

    protected $primaryKey = 'id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
