<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GlobalLeaderboardController extends Controller
{
  private $columns = array(
    [
      "key" => "rank",
      "text" => "Rank"
    ],
    [
      "key" => "split_battletag",
      "text" => "Battletag"
    ],
    [
      "key" => "region",
      "text" => "Region"
    ],
    [
      "key" => "win_rate",
      "text" => "Win Rate"
    ],
    [
      "key" => "rating",
      "text" => "Heroes Profile Rating"
    ],
    [
      "key" => "mmr",
      "text" => "MMR"
    ],
    [
      "key" => "tier",
      "text" => "Tier"
    ],
    [
      "key" => "games_played",
      "text" => "Games Played"
    ],
    [
      "key" => "most_played_hero",
      "text" => "Most Played Hero"
    ],
    [
      "key" => "most_played_build",
      "text" => "Most Played Build"
    ],
    [
      "key" => "hero_build_games_played",
      "text" => "Games Played With Hero"
    ]
  );

  private function splitColumn($column){
    $keys = Arr::pluck($column, 'key');
    return $keys;
  }

  public function show(){
    return view('Global.table',
    [
      'tableid' => 'leaderboard-table',
      'title' => 'Global Leaderboard', // Page title
      'paragraph' => 'Heroes Profile Leaderboard - View profiles of each player.', // Summary paragraph
      'tableheading' => 'Leaderboard', // Table heading
      'filtertype' => 'leaderboard',
      'columns' => $this->columns,
      'inputUrl' => "/getGlobalLeaderboardData",
      'columndata' => $this->splitColumn($this->columns),
      'page' => 'leaderboard',

      //Table Customizations
      'inputSortOrder' => array(4 => "desc"),
      'inputPaging' => true,
      'inputSearching' => true,
      'inputColReorder' => true,
      'inputFixedHeader' => true,
      'inputBInfo' => true,

    ]);
  }

  public function getData(Request $request){
    $filters_instance = \Filters::instance();
    $filters = $filters_instance->formatFilterData($request["data"], 0, 0);

    $leaderboard_type = $filters_instance->leaderboard_type;
    $hero = $filters_instance->single_hero;
    $role = $filters_instance->single_role;
    $region = $filters_instance->single_region;
    $season = $filters_instance->season;
    $game_type = $filters_instance->single_game_type;

    $leaderboardData = new \LeaderboardData($leaderboard_type, $hero, $role, $game_type, $season, $region);
    $return_data = $leaderboardData->getLeaderboardData();

    return $return_data;
  }
}
