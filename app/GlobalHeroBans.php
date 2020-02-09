<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroBans extends Model
{
  protected $table = 'global_hero_stats_bans';
  protected $primaryKey = 'global_stats_bans_id';
  public $timestamps = false;

  public function scopeFilters($query, $game_version, $game_type, $player_league_tier,
                               $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                               $region){
    if(count($game_version) > 0){
      $query->whereIn('game_version', $game_version);
    }

    if(count($game_type) > 0){
      $query->whereIn('game_type', $game_type);
    }

    if(count($player_league_tier) > 0){
      $query->whereIn('league_tier', $player_league_tier);
    }

    if(count($hero_league_tier) > 0){
      $query->whereIn('hero_league_tier', $hero_league_tier);
    }

    if(count($role_league_tier) > 0){
      $query->whereIn('role_league_tier', $role_league_tier);
    }

    if(count($game_map) > 0){
      $query->whereIn('game_map', $game_map);
    }

    if(count($hero_level) > 0){
      $query->whereIn('hero_level', $hero_level);
    }

    if(count($region) > 0){
      $query->whereIn('region', $region);
    }

    return $query;
  }
}
