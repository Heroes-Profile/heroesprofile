<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Real Storm League tier per player (1 bronze ... 6 master, 7 grandmaster), filled by Globals_Calculator. */
class GlobalHeroStatsRealRank extends Model
{
    protected $table = 'global_hero_stats_real_rank';

    protected $primaryKey = 'global_hero_stats_real_rank_id';

    protected $connection = 'heroesprofile_globals';

    public $timestamps = false;

    public function scopeFilterByGameVersion($query, $gameVersion)
    {
        return $query->whereIn('game_version', $gameVersion);
    }

    public function scopeFilterByRealLeagueTier($query, $realLeagueTier)
    {
        if (! empty($realLeagueTier)) {
            return $query->whereIn('real_league_tier', $realLeagueTier);
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

    public function scopeFilterByHero($query, $hero)
    {
        return $query->where('hero', $hero);
    }

    public function scopeExcludeMirror($query, $mirror)
    {
        if ($mirror == 1) {
            $query->whereIn('mirror', [0, 1]);
        } else {
            $query->where('mirror', 0);
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
