<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;

class GlobalHeroStatController extends Controller
{
  private $filters_instance;
  private $hero;

  public function getData(Request $request){
    $this->filters_instance = \Filters::instance();
    $filters = $this->filters_instance->formatFilterData($request["data"], 1);
    $this->hero = $request["hero"];

    switch ($request["page"]) {
    case "map":
        return $this->mapData($request);
        break;
    case 'matchups':
        return $this->matchupData($request);
        break;
    case 'talent-details':
        return $this->talentDetailData($request);
        break;
    case 'talent-builds':
        return $this->talentBuildData($request);
        break;
    default:
        return "Invalid";
        break;
      }
  }

  private function mapData($request){
    $game_versions_minor = $this->filters_instance->game_versions_minor;
    $game_type = $this->filters_instance->multi_game_type;
    $region = $this->filters_instance->multi_region;
    $game_map = $this->filters_instance->game_map;
    $hero_level = $this->filters_instance->hero_level;
    $stat_type = $this->filters_instance->stat_type;
    $player_league_tier = $this->filters_instance->player_league_tier;
    $hero_league_tier = $this->filters_instance->hero_league_tier;
    $role_league_tier = $this->filters_instance->role_league_tier;
    $mirror = $this->filters_instance->mirror;
    $hero = $this->hero;

    $page = "GlobalHeroMaps";
    $cache =  $page .
              "-" . $hero .
              "-" . implode(",", $game_versions_minor) .
              "-" . implode(",", $game_type) .
              "-" . implode(",", $region) .
              "-" . implode(",", $game_map) .
              "-" . implode(",", $hero_level) .
              "-" . $stat_type .
              "-"  . implode(",", $player_league_tier) .
              "-"  . implode(",", $hero_league_tier) .
              "-"  . implode(",", $role_league_tier) .
              "-"  . $mirror;

    $return_data = Cache::remember($cache, calculateCacheTime($this->filters_instance->timeframe_type, $this->filters_instance->game_versions_minor),
      function () use ($hero, $game_versions_minor, $game_type, $region, $game_map, $hero_level, $stat_type, $player_league_tier, $hero_league_tier,
      $role_league_tier, $mirror){

      $global_data = \GlobalHeroStatMapData::instance($hero, $game_versions_minor, $game_type, $region, $game_map,
                                            $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getGlobalHeroStatMapData();
      return $return_data;
    });
    $return_array["data"] = $return_data;
    return $return_array;
  }

  private function matchupData($request){
    $game_versions_minor = $this->filters_instance->game_versions_minor;
    $game_type = $this->filters_instance->multi_game_type;
    $region = $this->filters_instance->multi_region;
    $game_map = $this->filters_instance->game_map;
    $hero_level = $this->filters_instance->hero_level;
    $stat_type = $this->filters_instance->stat_type;
    $player_league_tier = $this->filters_instance->player_league_tier;
    $hero_league_tier = $this->filters_instance->hero_league_tier;
    $role_league_tier = $this->filters_instance->role_league_tier;
    $mirror = $this->filters_instance->mirror;
    $hero = $this->hero;

    $page = "GlobalHeroMatchups";
    $cache =  $page .
              "-" . $hero .
              "-" . implode(",", $game_versions_minor) .
              "-" . implode(",", $game_type) .
              "-" . implode(",", $region) .
              "-" . implode(",", $game_map) .
              "-" . implode(",", $hero_level) .
              "-" . $stat_type .
              "-"  . implode(",", $player_league_tier) .
              "-"  . implode(",", $hero_league_tier) .
              "-"  . implode(",", $role_league_tier) .
              "-"  . $mirror;

    $return_data = Cache::remember($cache, calculateCacheTime($this->filters_instance->timeframe_type, $this->filters_instance->game_versions_minor),
      function () use ($hero, $game_versions_minor, $game_type, $region, $game_map, $hero_level, $stat_type, $player_league_tier, $hero_league_tier,
      $role_league_tier, $mirror){

      $global_data = \GlobalHeroStatMatchupData::instance($hero, $game_versions_minor, $game_type, $region, $game_map,
                                            $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getGlobalHeroStatMatchupData();
      return $return_data;
    });
    $return_array["data"] = $return_data;
    return $return_array;
  }

  private function talentDetailData($request){
    $game_versions_minor = $this->filters_instance->game_versions_minor;
    $game_type = $this->filters_instance->multi_game_type;
    $region = $this->filters_instance->multi_region;
    $game_map = $this->filters_instance->game_map;
    $hero_level = $this->filters_instance->hero_level;
    $stat_type = $this->filters_instance->stat_type;
    $player_league_tier = $this->filters_instance->player_league_tier;
    $hero_league_tier = $this->filters_instance->hero_league_tier;
    $role_league_tier = $this->filters_instance->role_league_tier;
    $mirror = $this->filters_instance->mirror;
    $hero = $this->hero;

    $page = "GlobalHeroTalentDetails";
    $cache =  $page .
              "-" . $hero .
              "-" . implode(",", $game_versions_minor) .
              "-" . implode(",", $game_type) .
              "-" . implode(",", $region) .
              "-" . implode(",", $game_map) .
              "-" . implode(",", $hero_level) .
              "-" . $stat_type .
              "-"  . implode(",", $player_league_tier) .
              "-"  . implode(",", $hero_league_tier) .
              "-"  . implode(",", $role_league_tier) .
              "-"  . $mirror;


    $return_data = Cache::remember($cache, calculateCacheTime($this->filters_instance->timeframe_type, $this->filters_instance->game_versions_minor),
      function () use ($hero, $game_versions_minor, $game_type, $region, $game_map, $hero_level, $stat_type, $player_league_tier, $hero_league_tier,
      $role_league_tier, $mirror){

      $global_data_details = \GlobalHeroTalentDetailsData::instance($hero, $game_versions_minor, $game_type, $player_league_tier,
                                          $hero_league_tier, $role_league_tier, $game_map, $hero_level, $mirror, $region);
      $return_data = $global_data_details->getGlobalTalentDetailData();
      return $return_data;
    });
    /*
    $split_data[1] = $this->splitTalentBuildsOnLevel(1, $return_data);
    $split_data[4] = $this->splitTalentBuildsOnLevel(4, $return_data);
    $split_data[7] = $this->splitTalentBuildsOnLevel(7, $return_data);
    $split_data[10] = $this->splitTalentBuildsOnLevel(10, $return_data);
    $split_data[13] = $this->splitTalentBuildsOnLevel(13, $return_data);
    $split_data[16] = $this->splitTalentBuildsOnLevel(16, $return_data);
    $split_data[20] = $this->splitTalentBuildsOnLevel(20, $return_data);
    */

    return $return_data;
  }

  private function splitTalentBuildsOnLevel($level, $data){
    list($level_data, $unfeatured) = $data->partition(function($item) use ($level) {
        return $item->level == $level;
    });
    $counter = 0;
    $return_data = array();
    foreach ($level_data as $key => $value){
      $return_data[$counter] = $value;
      $counter++;
    }
    return $return_data;
  }

  private function talentBuildData($request){
    $game_versions_minor = $this->filters_instance->game_versions_minor;
    $game_type = $this->filters_instance->multi_game_type;
    $region = $this->filters_instance->multi_region;
    $game_map = $this->filters_instance->game_map;
    $hero_level = $this->filters_instance->hero_level;
    $stat_type = $this->filters_instance->stat_type;
    $player_league_tier = $this->filters_instance->player_league_tier;
    $hero_league_tier = $this->filters_instance->hero_league_tier;
    $role_league_tier = $this->filters_instance->role_league_tier;
    $mirror = $this->filters_instance->mirror;
    $hero = $this->hero;

    $page = "GlobalHeroTalentBuilds";
    $cache =  $page .
              "-" . $hero .
              "-" . implode(",", $game_versions_minor) .
              "-" . implode(",", $game_type) .
              "-" . implode(",", $region) .
              "-" . implode(",", $game_map) .
              "-" . implode(",", $hero_level) .
              "-" . $stat_type .
              "-"  . implode(",", $player_league_tier) .
              "-"  . implode(",", $hero_league_tier) .
              "-"  . implode(",", $role_league_tier) .
              "-"  . $mirror .
              "-"  . $type;


    $return_data = Cache::remember($cache, calculateCacheTime($this->filters_instance->timeframe_type, $this->filters_instance->game_versions_minor),
    function () use ($hero, $game_versions_minor, $game_type, $region, $game_map, $hero_level, $stat_type, $player_league_tier, $hero_league_tier,
    $role_league_tier, $mirror, $type){

      $global_data_builds = \GlobalHeroTalentBuildsData::instance($hero, $game_versions_minor, $game_type, $player_league_tier,
                                          $hero_league_tier, $role_league_tier, $game_map, $hero_level, $mirror, $region);
      $return_data = $global_data_builds->getGlobalHeroTalentData($type);
      return $return_data;
    });

    return $return_data;
  }
}
