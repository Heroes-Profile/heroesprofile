<?php

namespace App\Models\Esports\Other;

use Illuminate\Database\Eloquent\Model;

class Replay extends Model
{
    protected $table = 'replay';

    protected $primaryKey = 'replayID';

    protected $connection = 'heroesprofile_esports_other';

    public $timestamps = false;
}
