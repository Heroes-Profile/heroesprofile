<?php

namespace App\Models\Esports\Other;

use Illuminate\Database\Eloquent\Model;

class Regions extends Model
{
    protected $table = 'regions';

    protected $primaryKey = 'region_id';

    protected $connection = 'heroesprofile_esports_other';

    public $timestamps = false;
}
