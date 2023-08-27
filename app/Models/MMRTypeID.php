<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MMRTypeID extends Model
{
    protected $table = 'mmr_type_ids'; 
    protected $primaryKey = 'mmr_type_table_id';
    protected $connection = 'heroesprofile';

    public $timestamps = false;
}
