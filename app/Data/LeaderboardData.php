<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;
use Cache;
use Session;

/*TO DO
* Need to pass through offset data so that you can get more players.
* Right now it returns 250, but I store more than 250 in the DB
*/

class LeaderboardData
{
  private $game_type;
  private $season;
  private $region;
  private $type;

  public static function instance($game_type, $season, $region, $type)
  {
      return new LeaderboardData($game_type, $season, $region, $type);
  }


  public function __construct($game_type, $season, $region, $type) {
    $this->game_type = $game_type;
    $this->season = $season;
    $this->region = $region;
    $this->type = $type;
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

    $leaderboard_data = \App\Models\Leaderboard::Filters($this->game_type, $this->season, $this->region, $mmr_id, $this->getMaxCacheNumber())
                          ->select('rank', 'split_battletag', 'battletag', 'blizz_id', 'region', 'win_rate', 'win', 'loss', 'games_played', 'conservative_rating', 'rating', DB::raw('name as most_played_hero') , 'hero_build_games_played')
                          ->join('heroesprofile.heroes', 'heroesprofile.heroes.id', '=', 'heroesprofile_cache.leaderboard.most_played_hero')
                          ->get();
    return $leaderboard_data;
  }

  private function getMaxCacheNumber(){
    $max_cache_number = \App\Models\TableCacheValue::Filters('leaderboard', $this->season)
                          ->select(DB::raw('MAX(cache_number) as max_cache_number'))
                          ->get();
    return $max_cache_number[0]["max_cache_number"];
  }
}
