<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    protected $table = 'awards';

    protected $primaryKey = 'award_table_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
