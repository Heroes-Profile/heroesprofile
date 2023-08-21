<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Replay extends Model
{
    protected $table = 'replay';
    protected $primaryKey = 'replayID';
    public $timestamps = false;
    protected $connection = 'heroesprofile';


    public function players()
    {
        return $this->hasMany(Player::class, 'replayID', 'replayID');
    }

    public function map()
    {
        return $this->belongsTo(Map::class, 'game_map', 'map_id');
    }
}
