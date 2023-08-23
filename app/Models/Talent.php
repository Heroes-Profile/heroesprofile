<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Talent extends Model
{
    protected $table = 'talents';
    protected $primaryKey = 'talents_id';
    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
