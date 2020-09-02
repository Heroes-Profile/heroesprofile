<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Cache;

class GlobalHeroStatMapController extends Controller
{
  private $hero = "Abathur";


  private $columns = array(
    [
      "key" => "game_map",
      "text" => "Map"
    ],
    [
      "key" => "win_rate",
      "text" => "Win Rate %"
    ],
    [
      "key" => "popularity",
      "text" => "Popularity %"
    ],
    [
      "key" => "ban_rate",
      "text" => "Ban Rate %"
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
    ]
  );


  private function splitColumn($column){
    $keys = Arr::pluck($column, 'key');
    return $keys;
  }


  public function show(){
    return view('Global.table',
    [
      'tableid' => 'stats-table',
      'title' => 'Global Map Stats', // Page title
      'paragraph' => $this->hero . ' Map win rates based on differing increments, stat types, game type, or league tier.', // Summary paragraph
      'tableheading' => 'Win Rates', // Table heading
      'filtertype' => 'global_stats',
      'columns' => $this->columns,
      'inputUrl' => "/getGlobalHeroStatMapData",
      'columndata' => $this->splitColumn($this->columns),
      'page' => 'stat',
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

  public function getData(Request $request){
    $filters_instance = \Filters::instance();
    $filters = $filters_instance->formatFilterData($request["data"], 1, 0);

    $hero = $filters_instance->single_hero;
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



    $page = "GlobalStatMaps";
    $cache =  $page .
              "|" . $hero .
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
    $cache_time = 0;

    $return_data = Cache::remember($cache, $cache_time, function () use ($hero, $game_versions_minor, $game_type, $region, $game_map,
                                          $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror){
      $global_data = new \GlobalHeroStatMapData($hero, $game_versions_minor, $game_type, $region, $game_map,
                                            $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
      $return_data = $global_data->getGlobalHeroStatMapData();
      return $return_data;
    });

    //Need to add filtering for heroes and roles here

    return $return_data;



  }
}
