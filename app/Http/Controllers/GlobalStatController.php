<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Cache;

class GlobalStatController extends Controller
{
  private $columns = array(
    [
      "key" => "hero",
      "text" => "Hero"
    ],
    [
      "key" => "win_rate",
      "text" => "Win Rate"
    ],
    [
      "key" => "change",
      "text" => "Change"
    ],
    [
      "key" => "popularity",
      "text" => "Popularity"
    ],
    [
      "key" => "pick_rate",
      "text" => "Pick Rate"
    ],
    [
      "key" => "ban_rate",
      "text" => "Ban Rate"
    ],
    [
      "key" => "influence",
      "text" => "Influence"
    ],
    [
      "key" => "games_played",
      "text" => "Games Played"
    ],
    [
      "key" => "wins",
      "text" => "Wins"
    ],
    [
      "key" => "losses",
      "text" => "Losses"
    ],
    [
      "key" => "games_banned",
      "text" => "Games Banned"
    ]
  );
  
  public function show(){
    return view('Global.table',
    [
      'tableid' => 'stats-table',
      'title' => 'Global Stats', // Page title
      'paragraph' => 'Hero win rates based on differing increments, stat types, game type, or league tier.', // Summary paragraph
      'tableheading' => 'Win Rates', // Table heading
      'filtertype' => 'global_stats',
      'columns' => $this->columns,
      'inputUrl' => "/getGlobalStatData",
      'columndata' => $this->splitColumn($this->columns),
      'page' => 'stat',


      //Table Customizations
      'inputSortOrder' => array(1 => "desc"),
      'inputPaging' => false,
      'inputSearching' => false,
      'inputColReorder' => true,
      'inputFixedHeader' => true,
      'inputBInfo' => false,

    ]);
  }



  private function splitColumn($column){
    $keys = Arr::pluck($column, 'key');
    return $keys;
  }

  public function getData(Request $request){
    $filters_instance = \Filters::instance();
    $filters = $filters_instance->formatFilterData($request["data"], 1);

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

    //Need to add filtering for heroes and roles here
    return $return_data;
  }
}
