<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroDraftOrder extends Model
{
    protected $table = 'global_hero_draft_order';

    protected $primaryKey = 'global_hero_draft_order_id';

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

    public function scopeFilterByHeroLeagueTier($query, $heroLeagueTier)
    {
        if (! empty($heroLeagueTier)) {
            return $query->whereIn('hero_league_tier', $heroLeagueTier);
        }

        return $query;
    }

    public function scopeFilterByRoleLeagueTier($query, $roleLeagueTier)
    {
        if (! empty($roleLeagueTier)) {
            return $query->whereIn('role_league_tier', $roleLeagueTier);
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

    public function scopeFilterByHeroLevel($query, $heroLevel)
    {
        if (! empty($heroLevel)) {
            return $query->whereIn('hero_level', $heroLevel);
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

    public function scopeFilterByHero($query, $hero)
    {
        return $query->where('hero', $hero);
    }
}
