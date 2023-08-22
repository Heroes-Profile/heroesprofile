<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameType extends Model
{
    protected $table = 'game_types';
    protected $primaryKey = 'type_id';
    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
