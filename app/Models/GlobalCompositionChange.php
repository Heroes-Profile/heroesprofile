<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalCompositionChange extends Model
{
    protected $table = 'global_composition_change';

    protected $primaryKey = 'global_composition_change_id';

    protected $connection = 'heroesprofile_cache';

    public $timestamps = false;

    public function scopeFilterByGameVersion($query, $gameVersion)
    {
        return $query->whereIn('game_version', $gameVersion);
    }

    public function scopeFilterByGameType($query, $gameType)
    {
        return $query->whereIn('game_type', $gameType);
    }

    public function scopeFilterByHero($query, $hero)
    {
        return $query->where('hero', $hero);
    }
}
