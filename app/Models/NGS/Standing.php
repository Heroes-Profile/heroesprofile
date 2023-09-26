<?php

namespace App\Models\NGS;

use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    protected $table = 'standings';
    protected $primaryKey = 'standings_id';
    protected $connection = 'heroesprofile_ngs';

    public $timestamps = false;
}
