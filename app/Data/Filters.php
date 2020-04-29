<?php
namespace App\Data;

class Filters
{
  public $timeframe_type;
  public $game_versions_minor = array();
  public $game_type = array();
  public $region = array();
  public $game_map = array();
  public $hero_level = array();
  public $role = array();
  public $hero = array();
  public $stat_type;
  public $player_league_tier = array();
  public $hero_league_tier = array();
  public $role_league_tier = array();
  public $mirror;


  public static function instance()
  {
    return new Filters();
  }

  public function formatFilterData($request){
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
            array_push($this->region, $request[$i]["value"]);
            break;
        case "stat_type":
            $this->stat_type = $request[$i]["value"];
            break;
        case "hero_level":
            array_push($this->hero_level, $request[$i]["value"]);
            break;
        case "role":
            array_push($this->role, $request[$i]["value"]);
            break;
        case "hero":
            array_push($this->hero, $request[$i]["value"]);
            break;
        case "game_type":
            array_push($this->game_type, $request[$i]["value"]);
            break;
        case "game_map":
            array_push($this->game_map, $request[$i]["value"]);
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
        default:
            return "Invalid";
            break;
          }
      }
    }
  }
}
