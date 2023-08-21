<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'player';
    protected $player_table_id = 'replayID';
    public $timestamps = false;
    protected $connection = 'heroesprofile';

    public function replay()
    {
        return $this->belongsTo(Replay::class, 'replayID', 'replayID');
    }
}