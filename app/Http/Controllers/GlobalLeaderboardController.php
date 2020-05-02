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
      'dataurl' => '/get_heroes_stats_table_data', // URL used for calling the table data
      'title' => 'Global Leaderboard', // Page title
      'paragraph' => 'Hero win rates based on differing increments, stat types, game type, or league tier.', // Summary paragraph
      'tableheading' => 'Win Rates', // Table heading
      'columns' => $this->columns,
      'inputUrl' => "/getGlobalLeaderboardData",
      'columndata' => $this->splitColumn($this->columns)

    ]);
  }

  public function getData(Request $request){
    $filters_instance = \Filters::instance();
    $filters = $filters_instance->formatFilterData($request["data"], 0);

    $leaderboard_type = $filters_instance->leaderboard_type;
    $hero = $filters_instance->single_hero;
    $role = $filters_instance->single_role;
    $region = $filters_instance->single_region;
    $season = $filters_instance->season;
    $game_type = $filters_instance->single_game_type;

    $leaderboardData = \LeaderboardData::instance($leaderboard_type, $hero, $role, $game_type, $season, $region);
    $return_data = $leaderboardData->getLeaderboardData();

    return $return_data;
  }
}
