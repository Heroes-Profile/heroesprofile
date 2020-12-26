<?php
namespace App\Data;

class GlobalHeroDraftOrderData
{
  private $game_versions_minor;
  private $game_type;
  private $player_league_tier;
  private $hero_league_tier;
  private $role_league_tier;
  private $game_map;
  private $hero_level;
  private $region;


  public function __construct($game_versions_minor, $game_type, $player_league_tier, $hero_league_tier, $role_league_tier, $game_map, $hero_level, $region) {
    $this->game_versions_minor = $game_versions_minor;
    $this->game_type = $game_type;
    $this->player_league_tier = $player_league_tier;
    $this->hero_league_tier = $hero_league_tier;
    $this->role_league_tier = $role_league_tier;
    $this->game_map = $game_map;
    $this->hero_level = $hero_level;
    $this->region = $region;
  }

  public function getData($pick_numbers){
    $global_draft_order = \App\Models\GlobalHeroDraftOrder::Filters($this->game_versions_minor,
                                                                    $this->game_type,
                                                                    $this->player_league_tier,
                                                                    $this->hero_league_tier,
                                                                    $this->role_league_tier,
                                                                    $this->game_map,
                                                                    $this->hero_level,
                                                                    $this->region
                                                                    )
                   ->selectRaw('name as hero, pick_number, SUM(count) as total')
                   ->where('hero', '<>', 0)
                   ->whereIn('pick_number', $pick_numbers)
                   ->groupBy('hero', 'pick_number')
                   ->get();
    $total_values = array();
    $return_data = array();

    for($i = 0; $i < count($global_draft_order); $i++){
      if(!isset($total_values[$global_draft_order[$i]["hero"]])){
        $total_values[$global_draft_order[$i]["hero"]] = 0;
      }
      $total_values[$global_draft_order[$i]["hero"]] += $global_draft_order[$i]["total"];
    }

    for($i = 0; $i < count($global_draft_order); $i++){
      $return_data[$global_draft_order[$i]["pick_number"]][$global_draft_order[$i]["hero"]] = $global_draft_order[$i]["total"] / $total_values[$global_draft_order[$i]["hero"]];
    }
    
    return $return_data;
  }
}
