<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroMatchupsAlly extends Model
{
  protected $table = 'global_hero_matchups_ally';
  protected $primaryKey = 'global_hero_matchups_ally_id';
  public $timestamps = false;

  public function scopeFilters($query, $hero, $game_versions_minor, $game_type, $region, $game_map,
                                      $hero_level, $player_league_tier, $hero_league_tier, $role_league_tier){
    $query->whereIn('game_version', $game_versions_minor);
    $query->whereIn('game_type', $game_type);

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
