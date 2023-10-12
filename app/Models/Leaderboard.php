<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    protected $table = 'leaderboard';

    protected $primaryKey = 'leaderboard_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    public function scopeFilterByGameType($query, $gameType)
    {
        return $query->where('game_type', $gameType);
    }

    public function scopeFilterBySeason($query, $season)
    {
        return $query->where('season', $season);
    }

    public function scopeFilterByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFilterByStackSize($query, $stackSize)
    {
        return $query->where('stack_size', $stackSize);
    }

    public function scopeFilterByRegion($query, $region)
    {
        if (! is_null($region)) {
            return $query->where('region', $region);
        }

        return $query;
    }
}
