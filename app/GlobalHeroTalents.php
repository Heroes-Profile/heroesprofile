<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GlobalHeroTalents extends Model
{
  protected $table = 'global_hero_talents';
  protected $primaryKey = 'global_talents_id';
  public $timestamps = false;

  /*
  public function talentCombination()
  {
    return $this->hasOne('App\TalentCombinations' , 'talent_combination_id', 'talent_combination_id');
  }
  */

  public function scopeFilters($query, $hero, $game_version, $game_type, $player_league_tier,
                               $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                               $mirror, $region){

    $query->join('talent_combinations', 'talent_combinations.talent_combination_id', '=', 'global_hero_talents.talent_combination_id');
    $query->whereIn('game_version', $game_version);
    $query->whereIn('game_type', $game_type);
    $query->where('global_hero_talents.hero', $hero);


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

    return $query;
  }
}
