<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prematch extends Model
{
    protected $table = 'prematch';

    protected $primaryKey = 'prematch_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
