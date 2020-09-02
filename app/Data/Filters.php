<?php
namespace App\Data;

class Filters
{
  //Generic

  public $timeframe_type;
  public $game_versions_minor = array();
  public $multi_game_type = array();
  public $multi_region = array();
  public $game_map = array();
  public $hero_level = array();
  public $multi_role = array();
  public $multi_hero = array();
  public $stat_type;
  public $player_league_tier = array();
  public $hero_league_tier = array();
  public $role_league_tier = array();
  public $mirror;

  //Leaderboard Specific
  public $leaderboard_type;


  public $single_hero;
  public $single_role;
  public $single_region;
  public $single_game_type;
  public $season;
  public $tier;

  public static function instance()
  {
    return new Filters();
  }

  public function formatFilterData($request, $singleOrMulti, $singleMultiHero){
    $this->timeframe_type = "minor";
    if(!is_null($request)){
      for($i = 0; $i < count($request); $i++){
        switch ($request[$i]["name"]) {
          case "timeframe":
              $this->timeframe_type = $request[$i]["value"];
              break;
          case "major_timeframe":
              $versions = getFilterVersions();
              $this->game_versions_minor = array_merge($this->game_versions_minor, $versions[$request[$i]["value"]]);
              break;
          case "minor_timeframe":
              array_push($this->game_versions_minor, $request[$i]["value"]);
              break;
          case "region":
              if($singleOrMulti){
                array_push($this->multi_region, $request[$i]["value"]);
              }else{
                $this->single_region =  $request[$i]["value"];
              }
              break;
          case "stat_type":
              $this->stat_type = $request[$i]["value"];
              break;
          case "hero_level":
              array_push($this->hero_level, $request[$i]["value"]);
              break;
          case "role":
              if($singleOrMulti){
                array_push($this->multi_role, $request[$i]["value"]);
              }else{
                $this->single_role = $request[$i]["value"];
              }
              break;
          case "hero":
              if($singleOrMulti && $singleMultiHero){
                array_push($this->multi_hero, $request[$i]["value"]);
              }else{
                $this->single_hero = $request[$i]["value"];
              }
              break;
          case "game_type":
              if($singleOrMulti){
                array_push($this->multi_game_type, $request[$i]["value"]);
              }else{
                $this->single_game_type = $request[$i]["value"];
              }
              break;
          case "game_map":
              array_push($this->game_map, $request[$i]["value"]);
              break;
          case "tier":
              $this->tier = $request[$i]["value"];
              break;
          case "player_rank":
              array_push($this->player_league_tier, $request[$i]["value"]);
              break;
          case "hero_rank":
              array_push($this->hero_league_tier, $request[$i]["value"]);
              break;
          case "role_rank":
              array_push($this->role_league_tier, $request[$i]["value"]);
              break;
          case "leaderboard_type":
              $this->leaderboard_type = $request[$i]["value"];
              break;
          case "season":
              $this->season = $request[$i]["value"];
              break;
          default:
              return "Invalid";
              break;
          }
      }
    }
  }
}
