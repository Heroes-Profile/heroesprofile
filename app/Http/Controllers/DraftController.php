<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cache;

class DraftController extends Controller
{
  //$bans = array();

  public function show(){
    $return_data = array();
    $page = "Drafter.draft";
    return view('Drafter.draft',
    [
      'title' => 'Drafter', // Page title
      'paragraph' => 'Drafter Paragraph', // Summary paragraph
      'filtertype' => 'drafter',
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
    $game_type = array(5);
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
    //$cache_time = 1;

    $data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $region, $game_map,
    $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
      $ban_data = new \GlobalStatData($game_versions_minor, $game_type, $region, $game_map,
      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $ban_data->getHeroBans();
      return $return_data;
    });








    $page = "DraftBanOrder";
    $cache =  $page .
    "|" . implode(",", $heroesPicked) .
    "|" . implode(",", $game_versions_minor) .
    "|" . implode(",", $game_type) .
    "|" . implode(",", $region) .
    "|" . implode(",", $game_map) .
    "|" . implode(",", $hero_level) .
    "|"  . implode(",", $player_league_tier) .
    "|"  . implode(",", $hero_league_tier) .
    "|"  . implode(",", $role_league_tier);

    $ban_draft_order_data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $player_league_tier, $hero_league_tier, $role_league_tier, $game_map, $hero_level, $region){
      $ban_data = new \GlobalHeroDraftOrderData($game_versions_minor, $game_type, $player_league_tier, $hero_league_tier, $role_league_tier, $game_map, $hero_level, $region);
      $return_data = $ban_data->getData(array(0, 1, 2, 3));
      return $return_data;
    });

    if(isset($ban_draft_order_data[$request["currentPickNumber"]])){
      $current_pick_data = $ban_draft_order_data[$request["currentPickNumber"]];
    }else{
      $current_pick_data = array();
    }



    $page = "DraftInitialData";
    $cache =  $page .
    //"|" . implode(",", $heroesPicked) .
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



    $hero_win_rate_data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $region, $game_map,
    $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror, $current_pick_data){
      $global_data = new \GlobalStatData($game_versions_minor, $game_type, $region, $game_map,
      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getGlobalHeroStatData($current_pick_data);
      //$return_data = $global_data->getGlobalHeroStatData($current_pick_data);
      return $return_data;
    });


    $hero_win_rate_data_array = array();
    $total_games = 0;
    for($i = 0; $i < count($hero_win_rate_data); $i++){
      $hero_win_rate_data_array[$hero_win_rate_data[$i]["hero"]] = $hero_win_rate_data[$i];
      $total_games += $hero_win_rate_data[$i]["games_played"];
    }

    $total_games /= 10;

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

        $return_data[$counter]["games_played"] = $bans;
        if(!isset($current_pick_data[$hero])){
          $return_data[$counter]["bans"] = 0;
          $return_data[$counter]["pick_order_percent"] = 0;
        }else{
          $return_data[$counter]["bans"] = $bans * $current_pick_data[$hero];
          $return_data[$counter]["pick_order_percent"] = number_format(($current_pick_data[$hero] * 100), 2);
        }

        if($max_value < $return_data[$counter]["bans"]){
          $max_value = $return_data[$counter]["bans"];
        }

        if(isset($hero_win_rate_data_array[$hero])){
          $return_data[$counter]["win_rate"] = $hero_win_rate_data_array[$hero]["win_rate"];
          $return_data[$counter]["win_rate_confidence"] = $hero_win_rate_data_array[$hero]["win_rate_confidence"];
          $return_data[$counter]["influence"] = $hero_win_rate_data_array[$hero]["influence"];
          $return_data[$counter]["ban_rate"] = $hero_win_rate_data_array[$hero]["ban_rate"];
        }else{
          $return_data[$counter]["win_rate"] = 0;
          $return_data[$counter]["win_rate_confidence"] = 0;
          $return_data[$counter]["influence"] = 0;
          $return_data[$counter]["ban_rate"] = 0;
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

      if($return_data[$i]["value"] > 50){
        $return_data[$i]["starred"] = "starred";
      }else{
        $return_data[$i]["starred"] = "";
      }
    }
    usort($return_data, [$this, 'cmp_value']);

    return view('Drafter.draftPicks', ['controller_hero_data' => $return_data, 'bans' => true]);
  }

  public function getInitialData(Request $request){
    $filters_instance = \Filters::instance();
    $filters = $filters_instance->formatFilterData($request["data"], 1, 1);

    $game_versions_minor = $filters_instance->game_versions_minor;
    $game_type = array(5);
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




    $page = "DraftPickOrder";
    $cache =  $page .
    "|" . implode(",", array(4, 5, 6, 7, 8, 11, 12, 13, 14, 15)) .
    "|" . implode(",", $game_versions_minor) .
    "|" . implode(",", $game_type) .
    "|" . implode(",", $region) .
    "|" . implode(",", $game_map) .
    "|" . implode(",", $hero_level) .
    "|"  . implode(",", $player_league_tier) .
    "|"  . implode(",", $hero_league_tier) .
    "|"  . implode(",", $role_league_tier);

    $cache_time = calculateCacheTime($filters_instance->timeframe_type, $filters_instance->game_versions_minor);
    //$cache_time = 1;

    $ban_draft_order_data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $player_league_tier, $hero_league_tier, $role_league_tier, $game_map, $hero_level, $region){
      $initial_data = new \GlobalHeroDraftOrderData($game_versions_minor, $game_type, $player_league_tier, $hero_league_tier, $role_league_tier, $game_map, $hero_level, $region);
      $return_data = $initial_data->getData(array(4, 5, 6, 7, 8, 11, 12, 13, 14, 15));
      return $return_data;
    });

    $current_pick_data = $ban_draft_order_data[$request["currentPickNumber"]];



    $page = "DraftInitialData";
    $cache =  $page .
    //"|" . implode(",", $heroesPicked) .
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



    $data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $region, $game_map,
    $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror, $current_pick_data){
      $global_data = new \GlobalStatData($game_versions_minor, $game_type, $region, $game_map,
      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getGlobalHeroStatData($current_pick_data);
      //$return_data = $global_data->getGlobalHeroStatData($current_pick_data);
      return $return_data;
    });

    $heroes = \App\Models\Hero::select('id', 'name', 'short_name', "new_role")->get();
    $hero_data = array();

    for($i = 0; $i < count($heroes); $i++){
      $hero_data[$heroes[$i]["name"]] =  $heroes[$i];
    }

    $max_value = 0;
    for($i = 0; $i < count($data); $i++){
      $hero_name = $hero_data[$data[$i]["hero"]]["name"];

      if(isset($current_pick_data[$hero_name])){
        $influence_adjusted = $data[$i]["influence"] * $current_pick_data[$hero_name];
      }else{
        $influence_adjusted = 0;
      }

      if(!in_array($hero_data[$data[$i]["hero"]]["id"], $heroesPicked)){
        if($max_value < $influence_adjusted){
          $max_value = $influence_adjusted;
        }
      }

    }

    for($i = 0; $i < count($data); $i++){
      $hero_name = $hero_data[$data[$i]["hero"]]["name"];
      if(isset($current_pick_data[$hero_name])){
        $influence_adjusted = $data[$i]["influence"] * $current_pick_data[$hero_name];
      }else{
        $influence_adjusted = 0;
      }


      $data[$i]["id"] = $hero_data[$data[$i]["hero"]]->id;
      $data[$i]["name"] = $hero_data[$data[$i]["hero"]]->name;
      $data[$i]["short_name"] = $hero_data[$data[$i]["hero"]]->short_name;
      $data[$i]["new_role"] = $hero_data[$data[$i]["hero"]]->new_role;

      $data[$i]["influence_adjusted"] = ($influence_adjusted / $max_value) * 100;

      if(isset($current_pick_data[$hero_name])){
        $data[$i]["pick_order_percent"] = number_format($current_pick_data[$hero_name] * 100, 2);
      }else{
        $data[$i]["pick_order_percent"] = 0;
      }
    }

    for($i = 0; $i < count($data); $i++){
      $data[$i]["value"] = number_format($data[$i]["influence_adjusted"], 2);
      $data[$i]["games_played"] = $data[$i]["games_played"];
      $data[$i]["influence"] = $data[$i]["influence"];
      $data[$i]["win_rate"] = $data[$i]["win_rate"];
      $data[$i]["win_rate_confidence"] = $data[$i]["win_rate_confidence"];
      $data[$i]["win_rate_confidence"] = $data[$i]["win_rate_confidence"];

      if($data[$i]["value"] > 50){
        $data[$i]["starred"] = "starred";
      }else{
        $data[$i]["starred"] = "";
      }
    }

    $return_data = array();
    $counter = 0;
    for($i = 0; $i < count($data); $i++){
      if($request["currentPickNumber"] == 4){
        if($data[$i]["id"] == 11 || $data[$i]["id"] == 18){
          continue;
        }
      }
      if(!in_array($data[$i]["id"], $heroesPicked)){
        if(in_array(11, $heroesPicked) || in_array(18, $heroesPicked)){
          if($data[$i]["id"] != 11 && $data[$i]["id"] != 18){
            $return_data[$counter] = $data[$i];
            $counter++;
          }
        }else{
          $return_data[$counter] = $data[$i];
          $counter++;
        }

      }
    }
    usort($return_data, [$this, 'cmp_value']);
    return view('Drafter.draftPicks', ['controller_hero_data' => $return_data, 'bans' => false]);
  }

  public function getCompositionData(Request $request){
    $filters_instance = \Filters::instance();
    $filters = $filters_instance->formatFilterData($request["data"], 1, 1);

    $game_versions_minor = $filters_instance->game_versions_minor;
    $game_type = array(5);
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



    $page = "DraftPickOrder";
    $cache =  $page .
    "|" . implode(",", array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15)) .
    "|" . implode(",", $game_versions_minor) .
    "|" . implode(",", $game_type) .
    "|" . implode(",", $region) .
    "|" . implode(",", $game_map) .
    "|" . implode(",", $hero_level) .
    "|"  . implode(",", $player_league_tier) .
    "|"  . implode(",", $hero_league_tier) .
    "|"  . implode(",", $role_league_tier);

    $cache_time = calculateCacheTime($filters_instance->timeframe_type, $filters_instance->game_versions_minor);
    //$cache_time = 1;

    $ban_draft_order_data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $player_league_tier, $hero_league_tier, $role_league_tier, $game_map, $hero_level, $region){
      $pick_data = new \GlobalHeroDraftOrderData($game_versions_minor, $game_type, $player_league_tier, $hero_league_tier, $role_league_tier, $game_map, $hero_level, $region);
      $return_data = $pick_data->getData(array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15));
      return $return_data;
    });
    $current_pick_data = $ban_draft_order_data[$request["currentPickNumber"]];


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

    $data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $region, $game_map,
                            $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror, $teamPicked){
      $global_data = new \GlobalCompositionData($game_versions_minor, $game_type, $region, $game_map,
      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getDataForDrafter($teamPicked);
      return $return_data;
    });


    $page = "DraftInitialData";
    $cache =  $page .
    //"|" . implode(",", $heroesPicked) .
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



    $hero_win_rate_data = Cache::remember($cache, $cache_time, function () use ($game_versions_minor, $game_type, $region, $game_map,
    $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror, $current_pick_data){
      $global_data = new \GlobalStatData($game_versions_minor, $game_type, $region, $game_map,
      $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getGlobalHeroStatData($current_pick_data);
      //$return_data = $global_data->getGlobalHeroStatData($current_pick_data);
      return $return_data;
    });


    $hero_win_rate_data_array = array();
    $total_games = 0;
    for($i = 0; $i < count($hero_win_rate_data); $i++){
      $hero_win_rate_data_array[$hero_win_rate_data[$i]["hero"]] = $hero_win_rate_data[$i];
      $total_games += $hero_win_rate_data[$i]["games_played"];
    }

    $heroes = \App\Models\Hero::select('id', 'name', 'short_name', "new_role")->get();
    $hero_data = array();

    for($i = 0; $i < count($heroes); $i++){
      $hero_data[$heroes[$i]["id"]] =  $heroes[$i];
    }

    $max_value = 0;
    for($i = 0; $i < count($data); $i++){

      $hero_name = $hero_data[$data[$i]["hero"]]["name"];
      if(!isset($current_pick_data[$hero_name])){
        $games_played = 0;
      }else{
        $games_played = $data[$i]["games_played"] * $current_pick_data[$hero_name];
      }

      if($max_value < $games_played){
        $max_value = $games_played;
      }
    }

    $return_data = array();
    $counter = 0;
    for($i = 0; $i < count($data); $i++){
      if(!in_array($data[$i]["hero"], $heroesPicked)){
        if(in_array(11, $heroesPicked) || in_array(18, $heroesPicked)){
          if($data[$i]["id"] != 11 && $data[$i]["id"] != 18){
            $hero_name = $hero_data[$data[$i]["hero"]]["name"];
            if(!isset($current_pick_data[$hero_name])){
              $games_played = 0;
            }else{
              $games_played = $data[$i]["games_played"] * $current_pick_data[$hero_name];
            }

            $return_data[$counter] = $hero_data[$data[$i]["hero"]];
            $return_data[$counter]["value"] = number_format(($games_played / $max_value) * 100, 2);

            if($return_data[$counter]["value"] > 75){
              $return_data[$counter]["starred"] = "starred";
            }else{
              $return_data[$counter]["starred"] = "";
            }
            $counter++;
          }
        }else{
          if($request["currentPickNumber"] == 4 || $request["currentPickNumber"] == 6 || $request["currentPickNumber"] == 8 || $request["currentPickNumber"] == 12 || $request["currentPickNumber"] == 14 || $request["currentPickNumber"] == 15){
            if($data[$i]["hero"] == 11 || $data[$i]["hero"] == 18){
              continue;
            }
          }
          $hero_name = $hero_data[$data[$i]["hero"]]["name"];
          if(!isset($current_pick_data[$hero_name])){
            $games_played = 0;
          }else{
            $games_played = $data[$i]["games_played"] * $current_pick_data[$hero_name];
          }

          $return_data[$counter] = $hero_data[$data[$i]["hero"]];
          $return_data[$counter]["value"] = number_format(($games_played / $max_value) * 100, 2);


          $return_data[$counter]["win_rate"] = $hero_win_rate_data_array[$hero_name]["win_rate"];
          $return_data[$counter]["win_rate_confidence"] = $hero_win_rate_data_array[$hero_name]["win_rate_confidence"];
          $return_data[$counter]["influence"] = $hero_win_rate_data_array[$hero_name]["influence"];
          $return_data[$counter]["games_played"] = $hero_win_rate_data_array[$hero_name]["games_played"];
          if(isset($current_pick_data[$hero_name])){
            $return_data[$counter]["pick_order_percent"] = number_format($current_pick_data[$hero_name] * 100, 2);
          }else{
            $return_data[$counter]["pick_order_percent"] = 0;
          }

          $return_data[$counter]["ban_rate"] = $hero_win_rate_data_array[$hero_name]["ban_rate"];

          if($return_data[$counter]["value"] > 75){
            $return_data[$counter]["starred"] = "starred";
          }else{
            $return_data[$counter]["starred"] = "";
          }
          $counter++;
        }


      }
    }

    for($i = 0; $i < count($heroes); $i++){
      $found = false;
      for($j = 0; $j < count($data); $j++){
        if($data[$j]["hero"] == $heroes[$i]["id"]){

          $found = true;
          break;
        }
      }

      if(!$found){


        if($request["currentPickNumber"] == 4 || $request["currentPickNumber"] == 6 || $request["currentPickNumber"] == 8 || $request["currentPickNumber"] == 12 || $request["currentPickNumber"] == 14 || $request["currentPickNumber"] == 15){
          if($heroes[$i]["id"] == 11 || $heroes[$i]["id"] == 18){
            continue;
          }
        }

        if(!in_array($heroes[$i]["id"], $heroesPicked)){
          if(in_array(11, $heroesPicked) || in_array(18, $heroesPicked)){
            if($heroes[$i]["id"] != 11 && $heroes[$i]["id"] != 18){
              $return_data[$counter]["starred"] = "";
              $return_data[$counter]["new_role"] = $heroes[$i]["new_role"];
              $return_data[$counter]["name"] = $heroes[$i]["name"];
              $return_data[$counter]["id"] = $heroes[$i]["id"];
              $return_data[$counter]["short_name"] = $heroes[$i]["short_name"];
              $return_data[$counter]["value"] = 0;


              $return_data[$counter]["win_rate"] = 0;
              $return_data[$counter]["win_rate_confidence"] = 0;
              $return_data[$counter]["influence"] = 0;
              $return_data[$counter]["games_played"] = 0;
              $return_data[$counter]["pick_order_percent"] = 0;
              $return_data[$counter]["ban_rate"] = 0;



              $counter++;



            }
          }else{
            $return_data[$counter]["starred"] = "";
            $return_data[$counter]["new_role"] = $heroes[$i]["new_role"];
            $return_data[$counter]["name"] = $heroes[$i]["name"];
            $return_data[$counter]["id"] = $heroes[$i]["id"];
            $return_data[$counter]["short_name"] = $heroes[$i]["short_name"];
            $return_data[$counter]["value"] = 0;

            $return_data[$counter]["win_rate"] = 0;
            $return_data[$counter]["win_rate_confidence"] = 0;
            $return_data[$counter]["influence"] = 0;
            $return_data[$counter]["games_played"] = 0;
            $return_data[$counter]["pick_order_percent"] = 0;
            $return_data[$counter]["ban_rate"] = 0;
            $counter++;
          }
        }
      }
    }
    usort($return_data, [$this, 'cmp_value']);
    $bans = false;
    if($request["currentPickNumber"] == 9 || $request["currentPickNumber"] == 10){
      $bans = true;
    }
    return view('Drafter.draftPicks', ['controller_hero_data' => $return_data, 'bans' => $bans]);
  }

  private function cmp_value( $a, $b ) {
    if($a["value"] ==  $b["value"] ){ return 0 ; }
    return ($a["value"] > $b["value"]) ? -1 : 1;
  }

}
