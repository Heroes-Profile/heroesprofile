<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroDraftOrderSummary extends Model
{
    protected $table = 'global_hero_draft_order_summary';

    protected $primaryKey = 'global_hero_draft_order_summary_id';

    protected $connection = 'heroesprofile_globals';

    public $timestamps = false;

    public function scopeFilterByGameVersion($query, $gameVersion)
    {
        return $query->whereIn('game_version', $gameVersion);
    }

    public function scopeFilterByGameType($query, $gameType)
    {
        return $query->whereIn('game_type', $gameType);
    }

    public function scopeFilterByLeagueTier($query, $leagueTier)
    {
        if (! empty($leagueTier)) {
            return $query->whereIn('league_tier', $leagueTier);
        }

        return $query;
    }

    public function scopeFilterByGameMap($query, $gameMap)
    {
        if (! empty($gameMap)) {
            return $query->whereIn('game_map', $gameMap);
        }

        return $query;
    }

    public function scopeFilterByRegion($query, $region)
    {
        if (! empty($region)) {
            return $query->whereIn('region', $region);
        }

        return $query;
    }
}
