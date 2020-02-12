<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;
use Cache;
use Session;
/*
use App\Battletag;
use App\LeagueBreakdown;
use App\LeagueTier;
use DateTime;
*/

class LeaderboardData
{
  private $game_type;
  private $season;
  private $region;
  private $type;
  private $hero;
  private $role;
  private $page;

  public static function instance($game_type, $season, $region, $type, $hero, $role, $page)
  {
      return new LeaderboardData($game_type, $season, $region, $type, $hero, $role, $page);
  }


  public function __construct($game_type, $season, $region, $type, $hero, $role, $page) {
    $this->game_type = $game_type;
    $this->season = $season;
    $this->region = $region;
    $this->type = $type;
    $this->hero = $hero;
    $this->role = $role;
    $this->page = $page;

  }

  public function getLeaderboardData(){
    $max_cache_number = $this->getMaxCacheNumber();


    if($this->type == "player"){
      $mmr_id = 10000;
    }else if($this->type == "hero"){
      $mmr_id = $this->hero;
    }else if($this->type == "role"){
      $mmr_id = Session::get("mmr_type_ids")[$this->role];
    }

    $leaderboard_data = \App\Leaderboard::Filters($this->game_type, $this->season, $this->region, $mmr_id, 1)
                          ->select('rank', 'split_battletag', 'battletag', 'blizz_id', 'region', 'win_rate', 'win', 'loss', 'games_played', 'conservative_rating', 'rating')
                          ->limit(250)
                          ->get();
    return $leaderboard_data;
  }

  private function getMaxCacheNumber(){
    $max_cache_number = \App\TableCacheValue::Filters($this->season)
                          ->select(DB::raw('MAX(cache_number) as max_cache_number'))
                          ->get();
    return $max_cache_number;
  }
}
