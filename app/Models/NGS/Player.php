<?php

namespace App\Models\NGS;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'player';
    protected $primaryKey = 'player_id';
    protected $connection = 'heroesprofile_ngs';

    public $timestamps = false;
    
    public function replay()
    {
        return $this->belongsTo(Replay::class, 'replayID', 'replayID');
    }
}
