<?php

namespace App\Models\NGS;

use Illuminate\Database\Eloquent\Model;

class Replay extends Model
{
    protected $table = 'replay';

    protected $primaryKey = 'replayID';

    protected $connection = 'heroesprofile_ngs';

    public $timestamps = false;

    public function players()
    {
        return $this->hasMany(Player::class, 'replayID', 'replayID');
    }
}
