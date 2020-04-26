<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;

class GlobalHeroStatController extends Controller
{
  public function getData(Request $request){
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
      }
  }

  private function mapData($request){
    //$timeframe = array("major");
    $timeframe = array("minor");
    //$game_versions = array("2.49", "2.48");
    $game_versions = array("2.49.2.77981");

    /*
    //Needs to be calculated/pulled from session
    $game_versions_minor = array('2.48.0.76437',
    '2.48.1.76517',
    '2.48.2.76753',
    '2.48.2.76781',
    '2.48.2.76893',
    '2.48.3.77205',
    '2.48.4.77406',
    '2.49.0.77525',
    '2.49.0.77548',
    '2.49.1.77662',
    '2.49.1.77692',
    '2.49.2.77981',
    '2.49.3.78256');
    */

    $hero = 4;
    $game_versions_minor = array('2.49.2.77981');
    $game_type = array("5");
    $region = array();
    $game_map = array();
    $hero_level = array();
    $stat_type = array();
    $player_league_tier = array();
    $hero_league_tier = array();
    $role_league_tier = array();
    $mirror = array(0);

    $page = "GlobalHeroMaps";
    $cache =  $page .
              "-" . $hero .
              "-" . implode(",", $timeframe) .
              "-" . implode(",", $game_versions) .
              "-" . implode(",", $game_type) .
              "-" . implode(",", $region) .
              "-" . implode(",", $game_map) .
              "-" . implode(",", $hero_level) .
              "-" . implode(",", $stat_type) .
              "-"  . implode(",", $player_league_tier) .
              "-"  . implode(",", $hero_league_tier) .
              "-"  . implode(",", $role_league_tier);

    $return_data = Cache::remember($cache, calcluateCacheTime($timeframe, $game_versions), function () use ($hero, $game_versions_minor, $game_type, $region, $game_map,
                                          $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
      $global_data = \GlobalHeroStatMapData::instance($hero, $game_versions_minor, $game_type, $region, $game_map,
                                            $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getGlobalHeroStatMapData();
      return $return_data;
    });
    $return_array["data"] = $return_data;
    return $return_array;
  }

  private function matchupData($request){
    //$timeframe = array("major");
    $timeframe = array("minor");
    //$game_versions = array("2.49", "2.48");
    $game_versions = array("2.49.2.77981");

    /*
    //Needs to be calculated/pulled from session
    $game_versions_minor = array('2.48.0.76437',
    '2.48.1.76517',
    '2.48.2.76753',
    '2.48.2.76781',
    '2.48.2.76893',
    '2.48.3.77205',
    '2.48.4.77406',
    '2.49.0.77525',
    '2.49.0.77548',
    '2.49.1.77662',
    '2.49.1.77692',
    '2.49.2.77981',
    '2.49.3.78256');
    */

    $hero = 4;
    $game_versions_minor = array('2.49.2.77981');
    $game_type = array("5");
    $region = array();
    $game_map = array();
    $hero_level = array();
    $stat_type = array();
    $player_league_tier = array();
    $hero_league_tier = array();
    $role_league_tier = array();
    $mirror = array(0);

    $page = "GlobalHeroMatchups";
    $cache =  $page .
              "-" . $hero .
              "-" . implode(",", $timeframe) .
              "-" . implode(",", $game_versions) .
              "-" . implode(",", $game_type) .
              "-" . implode(",", $region) .
              "-" . implode(",", $game_map) .
              "-" . implode(",", $hero_level) .
              "-" . implode(",", $stat_type) .
              "-"  . implode(",", $player_league_tier) .
              "-"  . implode(",", $hero_league_tier) .
              "-"  . implode(",", $role_league_tier);

    $return_data = Cache::remember($cache, calcluateCacheTime($timeframe, $game_versions), function () use ($hero, $game_versions_minor, $game_type, $region, $game_map,
                                          $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
      $global_data = \GlobalHeroStatMatchupData::instance($hero, $game_versions_minor, $game_type, $region, $game_map,
                                            $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getGlobalHeroStatMatchupData();
      return $return_data;
    });
    $return_array["data"] = $return_data;
    return $return_array;
  }

  private function talentDetailData($request){
    //$timeframe = array("major");
    $timeframe = array("minor");
    //$game_versions = array("2.49", "2.48");
    $game_versions = array("2.49.2.77981");

    /*
    //Needs to be calculated/pulled from session
    $game_versions_minor = array('2.48.0.76437',
    '2.48.1.76517',
    '2.48.2.76753',
    '2.48.2.76781',
    '2.48.2.76893',
    '2.48.3.77205',
    '2.48.4.77406',
    '2.49.0.77525',
    '2.49.0.77548',
    '2.49.1.77662',
    '2.49.1.77692',
    '2.49.2.77981',
    '2.49.3.78256');
    */

    $hero = 4;
    $game_versions_minor = array('2.49.2.77981');
    $game_type = array("5");
    $region = array();
    $game_map = array();
    $hero_level = array();
    $stat_type = array();
    $player_league_tier = array();
    $hero_league_tier = array();
    $role_league_tier = array();
    $mirror = array(0);


    $page = "GlobalHeroTalentDetails";
    $cache =  $page .
              "-" . $hero .
              "-" . implode(",", $timeframe) .
              "-" . implode(",", $game_versions) .
              "-" . implode(",", $game_type) .
              "-" . implode(",", $region) .
              "-" . implode(",", $game_map) .
              "-" . implode(",", $hero_level) .
              "-" . implode(",", $stat_type) .
              "-"  . implode(",", $player_league_tier) .
              "-"  . implode(",", $hero_league_tier) .
              "-"  . implode(",", $role_league_tier);


    $return_data = Cache::remember($cache, calcluateCacheTime($timeframe, $game_versions), function () use ($hero, $game_versions_minor, $game_type, $region, $game_map,
                                          $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
      $global_data_details = \GlobalHeroTalentDetailsData::instance($hero, $game_versions_minor, $game_type, $player_league_tier,
                                          $hero_league_tier, $role_league_tier, $game_map, $hero_level, $mirror, $region);
      $return_data = $global_data_details->getGlobalTalentDetailData();
      return $return_data;
    });

    $split_data[1] = $this->splitTalentBuildsOnLevel(1, $return_data);
    $split_data[4] = $this->splitTalentBuildsOnLevel(4, $return_data);
    $split_data[7] = $this->splitTalentBuildsOnLevel(7, $return_data);
    $split_data[10] = $this->splitTalentBuildsOnLevel(10, $return_data);
    $split_data[13] = $this->splitTalentBuildsOnLevel(13, $return_data);
    $split_data[16] = $this->splitTalentBuildsOnLevel(16, $return_data);
    $split_data[20] = $this->splitTalentBuildsOnLevel(20, $return_data);


    return $split_data;
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
    //$timeframe = array("major");
    $timeframe = array("minor");
    //$game_versions = array("2.49", "2.48");
    $game_versions = array("2.49.2.77981");

    /*
    //Needs to be calculated/pulled from session
    $game_versions_minor = array('2.48.0.76437',
    '2.48.1.76517',
    '2.48.2.76753',
    '2.48.2.76781',
    '2.48.2.76893',
    '2.48.3.77205',
    '2.48.4.77406',
    '2.49.0.77525',
    '2.49.0.77548',
    '2.49.1.77662',
    '2.49.1.77692',
    '2.49.2.77981',
    '2.49.3.78256');
    */

    $hero = 4;
    $game_versions_minor = array('2.49.2.77981');
    $game_type = array("5");
    $region = array();
    $game_map = array();
    $hero_level = array();
    $stat_type = array();
    $player_league_tier = array();
    $hero_league_tier = array();
    $role_league_tier = array();
    $mirror = array(0);
    $type = "Popular";

    $page = "GlobalHeroTalentBuilds";
    $cache =  $page .
              "-" . $hero .
              "-" . implode(",", $timeframe) .
              "-" . implode(",", $game_versions) .
              "-" . implode(",", $game_type) .
              "-" . implode(",", $region) .
              "-" . implode(",", $game_map) .
              "-" . implode(",", $hero_level) .
              "-" . implode(",", $stat_type) .
              "-"  . implode(",", $player_league_tier) .
              "-"  . implode(",", $hero_league_tier) .
              "-"  . implode(",", $role_league_tier) .
              "-"  . $type;


    $return_data = Cache::remember($cache, calcluateCacheTime($timeframe, $game_versions), function () use ($hero, $game_versions_minor, $game_type, $region, $game_map,
                                          $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror, $type){
      $global_data_builds = \GlobalHeroTalentBuildsData::instance($hero, $game_versions_minor, $game_type, $player_league_tier,
                                          $hero_league_tier, $role_league_tier, $game_map, $hero_level, $mirror, $region);
      $return_data = $global_data_builds->getGlobalHeroTalentData($type);
      return $return_data;
    });

    return $return_data;
  }
}
