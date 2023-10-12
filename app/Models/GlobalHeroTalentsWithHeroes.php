<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroTalentsWithHeroes extends Model
{
    protected $table = 'global_hero_talents_with_heroes';

    protected $primaryKey = 'global_hero_talents_with_heroes_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    public function talentInfo()
    {
        return $this->belongsTo(HeroesDataTalent::class, 'talent', 'talent_id');
    }

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

    public function scopeFilterByHero($query, $hero)
    {
        return $query->where('hero', $hero);
    }

    public function scopeFilterByAllyEnemy($query, $hero)
    {
        return $query->where('ally', $hero);
    }
}
