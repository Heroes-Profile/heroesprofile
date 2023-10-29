<?php

namespace App\Models\MastersClash;

use Illuminate\Database\Eloquent\Model;

class Replay extends Model
{
    protected $table = 'replay';

    protected $primaryKey = 'replayID';

    protected $connection = 'heroesprofile_mcl';

    public $timestamps = false;
}
