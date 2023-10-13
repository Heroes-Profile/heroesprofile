<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'player';

    protected $player_table_id = 'replayID';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    public function replay()
    {
        return $this->belongsTo(Replay::class, 'replayID', 'replayID');
    }
}
