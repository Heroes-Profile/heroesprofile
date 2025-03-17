<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroCompositions extends Model
{
    protected $table = 'global_hero_compositions';

    protected $primaryKey = 'global_hero_compositions_id';

    protected $connection = 'heroesprofile';

    public $timestamps = false;

    public function composition()
    {
        return $this->belongsTo(HeroComposition::class, 'hero_composition_id', 'hero_composition_id');
    }

    public function scopeFilterByGameVersion($query, $gameVersion)
    {
        return $query->whereIn('game_version', $gameVersion);
    }

    public function scopeFilterByGameType($query, $gameType)
    {
        return $query->whereIn('game_type', $gameType);
    }

    public function scopeFilterByGameMap($query, $gameMap)
    {
        if (! empty($gameMap)) {
            return $query->whereIn('game_map', $gameMap);
        }

        return $query;
    }

    public function scopeFilterByCompositionID($query, $compositionID)
    {
        return $query->where('hero_composition_id', $compositionID);
    }

    public function scopeFilterByHero($query, $hero)
    {
        return $query->when(! is_null($hero), function ($query) use ($hero) {
            return $query->where('hero', $hero);
        });
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
