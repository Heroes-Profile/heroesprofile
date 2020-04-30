<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GlobalLeaderboardController extends Controller
{
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
