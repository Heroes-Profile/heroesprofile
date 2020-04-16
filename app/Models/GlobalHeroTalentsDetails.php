<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroTalentsDetails extends Model
{
  protected $table = 'global_hero_talents_details';
  protected $primaryKey = 'global_hero_talent_details_id';
  public $timestamps = false;

  public function scopeFilters($query, $hero, $game_version, $game_type, $player_league_tier,
                               $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                               $mirror, $region){
    $query->join('heroes_data_talents', 'heroes_data_talents.talent_id', '=', 'global_hero_talents_details.talent');

    $query->whereIn('game_version', $game_version);
    $query->whereIn('game_type', $game_type);
    $query->where('hero', $hero);


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

    if(count($mirror) > 0){
      $query->whereIn('mirror', $mirror);
    }

    if(count($region) > 0){
      $query->whereIn('region', $region);
    }

    $query->where('talent', '<>', 0);
    return $query;
  }
}
