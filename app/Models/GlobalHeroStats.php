<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroStats extends Model
{
    protected $table = 'global_hero_stats';

    protected $primaryKey = 'global_hero_id';

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
        if (! is_null($leagueTier)) {
            return $query->whereIn('league_tier', $leagueTier);
        }

        return $query;
    }

    public function scopeFilterByHeroLeagueTier($query, $heroLeagueTier)
    {
        if (! is_null($heroLeagueTier)) {
            return $query->whereIn('hero_league_tier', $heroLeagueTier);
        }

        return $query;
    }

    public function scopeFilterByRoleLeagueTier($query, $roleLeagueTier)
    {
        if (! is_null($roleLeagueTier)) {
            return $query->whereIn('role_league_tier', $roleLeagueTier);
        }

        return $query;
    }

    public function scopeFilterByGameMap($query, $gameMap)
    {
        if (! is_null($gameMap)) {
            return $query->whereIn('game_map', $gameMap);
        }

        return $query;
    }

    public function scopeFilterByHeroLevel($query, $heroLevel)
    {
        if (! is_null($heroLevel)) {
            return $query->whereIn('hero_level', $heroLevel);
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
        if (! is_null($region)) {
            return $query->whereIn('region', $region);
        }

        return $query;
    }
}
