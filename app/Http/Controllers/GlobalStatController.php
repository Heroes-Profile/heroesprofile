<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;

class GlobalStatController extends Controller
{
    public function getData(Request $request){
      //$this->formatResponse($request["data"]);
      $filters_instance = \Filters::instance();
      $filters = $filters_instance->formatFilterData($request["data"]);

      $game_versions_minor = $filters_instance->game_versions_minor;
      $game_type = $filters_instance->game_type;
      $region = $filters_instance->region;
      $game_map = $filters_instance->game_map;
      $hero_level = $filters_instance->hero_level;
      $stat_type = $filters_instance->stat_type;
      $player_league_tier = $filters_instance->player_league_tier;
      $hero_league_tier = $filters_instance->hero_league_tier;
      $role_league_tier = $filters_instance->role_league_tier;
      $mirror = $filters_instance->mirror;


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


      $return_data = Cache::remember($cache, calculateCacheTime($filters_instance->timeframe_type, $filters_instance->game_versions_minor), function () use ($game_versions_minor, $game_type, $region, $game_map,
                                            $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
        $global_data = \GlobalStatData::instance($game_versions_minor, $game_type, $region, $game_map,
                                              $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
        $return_data = $global_data->getGlobalHeroStatData();
        return $return_data;
      });

      return $return_data;
    }
}
