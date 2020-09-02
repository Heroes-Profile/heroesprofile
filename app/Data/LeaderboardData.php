<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;

/*TO DO
* Need to pass through offset data so that you can get more players.
* Right now it returns 250, but I store more than 250 in the DB
*/

class LeaderboardData
{
  private $type;
  private $hero;
  private $role;
  private $game_type;
  private $season;
  private $region;
  private $tier;

  public function __construct($type, $hero, $role, $game_type, $season, $region, $tier) {
    $this->type = $type;
    $this->hero = $hero;
    $this->role = $role;
    $this->game_type = $game_type;
    $this->season = $season;
    $this->region = $region;
    $this->tier = $tier;
  }

  public function getLeaderboardData(){
    $max_cache_number = $this->getMaxCacheNumber();
    $mmr_type_ids = getMMRTypeIDs();
    if($this->type == "player"){
      $mmr_id = 10000;
    }else if($this->type == "hero"){
      $mmr_id = $this->hero;
    }else if($this->type == "role"){
      $mmr_id = $mmr_type_ids[$this->role];
    }

    $leaderboard_data = \App\Models\Leaderboard::Filters($this->game_type, $this->season, $this->region, $mmr_id, $this->getMaxCacheNumber())
                          ->select(
                            'rank',
                            'split_battletag',
                            'battletag',
                            'blizz_id',
                            'region',
                            'win_rate',
                            'win',
                            'loss',
                            'games_played',
                            'conservative_rating as mmr',
                            'rating',
                            'most_played_hero',
                            'level_one',
                            'level_four',
                            'level_seven',
                            'level_ten',
                            'level_thirteen',
                            'level_sixteen',
                            'level_twenty',
                            'hero_build_games_played'
                            )
                          ->get();

                          /*
                          print_r($leaderboard_data->toSql());
                          echo "<br>";
                          print_r($leaderboard_data->getBindings());
                          echo "<br>";
                          */
    $league_tiers = getLeagueTierBreakdown($this->game_type, $mmr_id);
    $heroes = getHeroesIDMap("id", "name");

    for($i = 0; $i < count($leaderboard_data); $i++){
      $leaderboard_data[$i]->tier = getRankSplit($leaderboard_data[$i]->mmr, $league_tiers);

      if($leaderboard_data[$i]->most_played_hero == 0){
        $leaderboard_data[$i]->most_played_hero = "";
      }else{
        $leaderboard_data[$i]->most_played_hero = $heroes[$leaderboard_data[$i]->most_played_hero];
      }

      $leaderboard_data[$i]->most_played_build = "";
    }
    $return_leaderboard_data = new \App\Models\Leaderboard;
    $counter = 0;

    if($this->tier != ""){
      for($i = 0; $i < count($leaderboard_data); $i++){
        if (strpos($leaderboard_data[$i]->tier, $this->tier) !== false) {
          $return_leaderboard_data[$counter++] = $leaderboard_data[$i];
        }
      }
    }else{
      $return_leaderboard_data = $leaderboard_data;
    }

    return $return_leaderboard_data;
  }

  private function getMaxCacheNumber(){
    $max_cache_number = \App\Models\TableCacheValue::Filters('leaderboard', $this->season)
                          ->selectRaw('MAX(cache_number) as max_cache_number')
                          ->get();
    return $max_cache_number[0]["max_cache_number"];
  }
}
