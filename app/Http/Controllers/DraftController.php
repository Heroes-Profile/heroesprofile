<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;

class DraftController extends Controller
{
  //$bans = array();

  public function show(){
    $return_data = array();
    return view('Drafter.draft',
    [
      'title' => 'Drafter', // Page title
      'paragraph' => 'Drafter Paragraph', // Summary paragraph
      'filtertype' => 'global_stats',
      'page' => 'drafter',
      'heroFilter' => false,
      'roleFilter' => false,
      'controller_hero_data' => $return_data
    ]);
  }


  public function getDraftBanData(Request $request){
    $filters_instance = \Filters::instance();
    $filters = $filters_instance->formatFilterData($request["data"], 1, 1);

    $game_versions_minor = $filters_instance->game_versions_minor;
    $game_type = $filters_instance->multi_game_type;
    $region = $filters_instance->multi_region;
    $game_map = $filters_instance->game_map;
    $hero_level = $filters_instance->hero_level;
    $stat_type = $filters_instance->stat_type;
    $player_league_tier = $filters_instance->player_league_tier;
    $hero_league_tier = $filters_instance->hero_league_tier;
    $role_league_tier = $filters_instance->role_league_tier;
    $mirror = $filters_instance->mirror;

    $heroesPicked = array();
    if(isset($request["heroesPicked"])){
      $heroesPicked = $request["heroesPicked"];
    }

    $page = "DraftBans";
    $cache =  $page .
    "|" . implode(",", $heroesPicked) .
    "|" . implode(",", $game_versions_minor) .
    "|" . implode(",", $game_type) .
    "|" . implode(",", $region) .
    "|" . implode(",", $game_map) .
    "|" . implode(",", $hero_level) .
    "|"  . implode(",", $player_league_tier) .
    "|"  . implode(",", $hero_league_tier) .
    "|"  . implode(",", $role_league_tier) .
    "|"  . $mirror;

    $cache_time = calculateCacheTime($filters_instance->timeframe_type, $filters_instance->game_versions_minor);
    $cache_time = 20000;

    $data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $region, $game_map,
    $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
      $ban_data = new \GlobalStatData($game_versions_minor, $game_type, $region, $game_map,
      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $ban_data->getHeroBans();
      return $return_data;
    });
    arsort($data);
    $max_value = 0;
    $return_data = array();
    $counter = 0;


    $heroes = \App\Models\Hero::select('id', 'name', 'short_name', "new_role")->get();
    $hero_data = array();

    for($i = 0; $i < count($heroes); $i++){
      $hero_data[$heroes[$i]["name"]] =  $heroes[$i];
    }


    foreach($data as $hero => $bans){
      if(!in_array($hero_data[$hero]->id, $heroesPicked)){
        $return_data[$counter]["hero"] = $hero;
        $return_data[$counter]["bans"] = $bans;

        if($max_value < $bans){
          $max_value = $bans;
        }
        $counter++;
      }

    }


    for($i = 0; $i < count($return_data); $i++){
      $return_data[$i]["id"] = $hero_data[$return_data[$i]["hero"]]->id;
      $return_data[$i]["name"] = $hero_data[$return_data[$i]["hero"]]->name;
      $return_data[$i]["short_name"] = $hero_data[$return_data[$i]["hero"]]->short_name;
      $return_data[$i]["new_role"] = $hero_data[$return_data[$i]["hero"]]->new_role;
      $return_data[$i]["value"] = number_format(($return_data[$i]["bans"] / $max_value) * 100, 2);
    }

    return view('Drafter.draftPicks', ['controller_hero_data' => $return_data]);
  }

  public function getInitialData(Request $request){
    $filters_instance = \Filters::instance();
    $filters = $filters_instance->formatFilterData($request["data"], 1, 1);

    $game_versions_minor = $filters_instance->game_versions_minor;
    $game_type = $filters_instance->multi_game_type;
    $region = $filters_instance->multi_region;
    $game_map = $filters_instance->game_map;
    $hero_level = $filters_instance->hero_level;
    $stat_type = $filters_instance->stat_type;
    $player_league_tier = $filters_instance->player_league_tier;
    $hero_league_tier = $filters_instance->hero_league_tier;
    $role_league_tier = $filters_instance->role_league_tier;
    $mirror = $filters_instance->mirror;

    $heroesPicked = array();
    if(isset($request["heroesPicked"])){
      $heroesPicked = $request["heroesPicked"];
    }

    $page = "DraftInitialData";
    $cache =  $page .
    "|" . implode(",", $heroesPicked) .
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

    $cache_time = calculateCacheTime($filters_instance->timeframe_type, $filters_instance->game_versions_minor);
    $cache_time = 20000;

    $data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $region, $game_map,
    $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
      $global_data = new \GlobalStatData($game_versions_minor, $game_type, $region, $game_map,
      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getGlobalHeroStatData();
      return $return_data;
    });
    usort($data, [$this, 'cmp_influence']);

    $heroes = \App\Models\Hero::select('id', 'name', 'short_name', "new_role")->get();
    $hero_data = array();

    for($i = 0; $i < count($heroes); $i++){
      $hero_data[$heroes[$i]["name"]] =  $heroes[$i];
    }

    $max_value = 0;
    for($i = 0; $i < count($data); $i++){
      if($max_value < $data[$i]["influence"]){
        $max_value = $data[$i]["influence"];
      }
    }
    for($i = 0; $i < count($data); $i++){
      $data[$i]["id"] = $hero_data[$data[$i]["hero"]]->id;
      $data[$i]["name"] = $hero_data[$data[$i]["hero"]]->name;
      $data[$i]["short_name"] = $hero_data[$data[$i]["hero"]]->short_name;
      $data[$i]["new_role"] = $hero_data[$data[$i]["hero"]]->new_role;
      $data[$i]["value"] = number_format(($data[$i]["influence"] / $max_value) * 100, 2);
    }

    $return_data = array();
    $counter = 0;
    for($i = 0; $i < count($data); $i++){
      if(!in_array($data[$i]["id"], $heroesPicked)){
        $return_data[$counter] = $data[$i];
        $counter++;
      }
    }
    return view('Drafter.draftPicks', ['controller_hero_data' => $return_data]);
  }

  public function getPickData(Request $request){
    $filters_instance = \Filters::instance();
    $filters = $filters_instance->formatFilterData($request["data"], 1, 1);

    $game_versions_minor = $filters_instance->game_versions_minor;
    $game_type = $filters_instance->multi_game_type;
    $region = $filters_instance->multi_region;
    $game_map = $filters_instance->game_map;
    $hero_level = $filters_instance->hero_level;
    $stat_type = $filters_instance->stat_type;
    $player_league_tier = $filters_instance->player_league_tier;
    $hero_league_tier = $filters_instance->hero_league_tier;
    $role_league_tier = $filters_instance->role_league_tier;
    $mirror = $filters_instance->mirror;

    $heroesPicked = array();
    if(isset($request["heroesPicked"])){
      $heroesPicked = $request["heroesPicked"];
    }

    $teamPicked = array();
    if(isset($request["teamPicks"])){
      $teamPicked = $request["teamPicks"];
    }

    $page = "PickData";
    $cache =  $page .
    "|" . implode(",", $heroesPicked) .
    "|" . implode(",", $teamPicked) .
    "|" . implode(",", $game_versions_minor) .
    "|" . implode(",", $game_type) .
    "|" . implode(",", $region) .
    "|" . implode(",", $game_map) .
    "|" . implode(",", $hero_level) .
    "|"  . implode(",", $player_league_tier) .
    "|"  . implode(",", $hero_league_tier) .
    "|"  . implode(",", $role_league_tier) .
    "|"  . $mirror;


    $cache_time = calculateCacheTime($filters_instance->timeframe_type, $filters_instance->game_versions_minor);
    $cache_time = 20000;

    $data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $region, $game_map,
                            $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror, $teamPicked){
      $global_data = new \GlobalCompositionData($game_versions_minor, $game_type, $region, $game_map,
      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getDataForDrafter($teamPicked);
      return $return_data;
    });

    $heroes = \App\Models\Hero::select('id', 'name', 'short_name', "new_role")->get();
    $hero_data = array();

    for($i = 0; $i < count($heroes); $i++){
      $hero_data[$heroes[$i]["id"]] =  $heroes[$i];
    }

    $max_value = 0;
    for($i = 0; $i < count($data); $i++){
      if($max_value < $data[$i]["games_played"]){
        $max_value = $data[$i]["games_played"];
      }
    }

    $return_data = array();
    $counter = 0;
    for($i = 0; $i < count($data); $i++){
      if(!in_array($data[$i]["hero"], $heroesPicked)){
        $return_data[$counter] = $hero_data[$data[$i]["hero"]];
        $return_data[$counter]["value"] = number_format(($data[$i]["games_played"] / $max_value) * 100, 2);
        $counter++;
      }
    }


    return view('Drafter.draftPicks', ['controller_hero_data' => $return_data]);
  }

  private function cmp_influence( $a, $b ) {
    if($a["influence"] ==  $b["influence"] ){
      return 0 ;
    }
    return ($a["influence"] > $b["influence"]) ? -1 : 1;
  }
}
