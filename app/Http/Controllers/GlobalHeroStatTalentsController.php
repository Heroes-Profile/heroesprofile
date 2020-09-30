<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;

class GlobalHeroStatTalentsController extends Controller
{
  private $filters_instance;
  private $hero;


  public function show(){
    return view('Global.Hero.stats',
    [
      'tableid' => 'stats-table',
      'title' => 'Global Map Stats', // Page title
      'paragraph' => $this->hero . ' Talent win rates based on differing increments, stat types, game type, or league tier.', // Summary paragraph
      'tableheading' => 'Win Rates', // Table heading
      'filtertype' => 'global_stats',
      //'columns' => $this->columns,
      'talentsInputUrl' => "/getGlobalHeroStatTalentData",
      'talentBuildsInputUrl' => "/getGlobalHeroStatTalentBuildsData",
      //'columndata' => $this->splitColumn($this->columns),
      'page' => 'stat',
      'heroFilter' => true,
      'roleFilter' => true,
      'hero' => $this->hero,

      //Table Customizations
      'inputSortOrder' => array(1 => "desc"),
      'inputPaging' => false,
      'inputSearching' => false,
      'inputColReorder' => true,
      'inputFixedHeader' => true,
      'inputBInfo' => false,
    ]);
  }

  public function talentDetailData(Request $request){
    $this->filters_instance = \Filters::instance();
    $filters = $this->filters_instance->formatFilterData($request["data"], 1, 0);
    $this->hero = $request["hero"];

    $this->hero = 1; //temporary

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

    $cache_time = calculateCacheTime($this->filters_instance->timeframe_type, $this->filters_instance->game_versions_minor);
    //$cache_time = 0; //for testing

    $return_data = Cache::remember($cache, $cache_time, function () use ($hero, $game_versions_minor, $game_type, $region, $game_map,
                                          $hero_level, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
      $global_data_details = new \GlobalHeroTalentDetailsData($hero, $game_versions_minor, $game_type, $player_league_tier,
                                          $hero_league_tier, $role_league_tier, $game_map, $hero_level, $mirror, $region);
      $return_data = $global_data_details->getGlobalTalentDetailData();
      return $return_data;
    });
    return $return_data;
  }


  public function talentBuildData(Request $request){
    $this->filters_instance = \Filters::instance();
    $filters = $this->filters_instance->formatFilterData($request["data"], 1, 0);
    $this->hero = $request["hero"];

    $this->hero = 1; //temporary

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

    $type = "Popular"; //Need to get this out of filter later
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

    $cache_time = calculateCacheTime($this->filters_instance->timeframe_type, $this->filters_instance->game_versions_minor);
    //$cache_time = 0; //for testing


    $return_data = Cache::remember($cache, $cache_time, function () use ($hero, $game_versions_minor, $game_type, $region,
                                          $game_map, $hero_level, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror, $type){
      $global_data_builds = new \GlobalHeroTalentBuildsData($hero, $game_versions_minor, $game_type, $player_league_tier,
                                          $hero_league_tier, $role_league_tier, $game_map, $hero_level, $mirror, $region);
      $return_data = $global_data_builds->getGlobalHeroTalentData($type);
      return $return_data;
    });

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

}
