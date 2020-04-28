<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;

class GlobalStatController extends Controller
{
    private $timeframe_type;
    private $game_versions_minor = array();
    private $game_type = array();
    private $region = array();
    private $game_map = array();
    private $hero_level = array();
    private $role = array();
    private $hero = array();
    private $stat_type;
    private $player_league_tier = array();
    private $hero_league_tier = array();
    private $role_league_tier = array();
    private $mirror;


    private function formatResponse($request){
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


    public function getData(Request $request){
      $this->formatResponse($request["data"]);
      $game_versions_minor = $this->game_versions_minor;
      $game_type = $this->game_type;
      $region = $this->region;
      $game_map = $this->game_map;
      $hero_level = $this->hero_level;
      $stat_type = $this->stat_type;
      $player_league_tier = $this->player_league_tier;
      $hero_league_tier = $this->hero_league_tier;
      $role_league_tier = $this->role_league_tier;
      $mirror = $this->mirror;


      $page = "GlobalStats";
      $cache =  $page .
                "|" . implode(",", $game_versions_minor) .
                "|" . implode(",", $game_type) .
                "|" . implode(",", $region) .
                "|" . implode(",", $game_map) .
                "|" . implode(",", $hero_level) .
                "|" . $stat_type .
                "|"  . implode(",", $player_league_tier) .
                "|"  . implode(",", $hero_league_tier) .
                "|"  . implode(",", $role_league_tier) .
                "|"  . $mirror;

                
      $return_data = Cache::remember($cache, calculateCacheTime($this->timeframe_type, $this->game_versions_minor), function () use ($game_versions_minor, $game_type, $region, $game_map,
                                            $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
        $global_data = \GlobalHeroStatsData::instance($game_versions_minor, $game_type, $region, $game_map,
                                              $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
        $return_data = $global_data->getGlobalHeroStatData();
        return $return_data;
      });

      return $return_data;
    }
}
