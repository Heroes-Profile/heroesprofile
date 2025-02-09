<?php

namespace App\Models\Other;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $table = 'series';

    protected $primaryKey = 'series_id';

    protected $connection = 'heroesprofile_esports_other';

    public $timestamps = false;
}
