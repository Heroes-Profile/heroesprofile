<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Battletag extends Model
{
    protected $table = 'battletags';
    protected $primaryKey = 'player_id';
    public $timestamps = false;
}
