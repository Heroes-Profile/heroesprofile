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
    if($this->type == "player"){
      $mmr_id = 10000;
    }else if($this->type == "hero"){
      //$mmr_id = $_SESSION['mmr_type_ids'][$this->hero];
    }else if($this->type == "role"){
    //  $mmr_id = $_SESSION['mmr_type_ids'][$this->role];
    }

    $query = DB::table('heroesprofile_cache.table_cache_value');
    $query->select(DB::raw('MAX(cache_number) as max_cache_number'));
    $query->where('date_cached', '>=', Session::get("season_dates")[$this->season]["start_date"]);
    $query->where('date_cached', '<=', Session::get("season_dates")[$this->season]["end_date"]);
    $query->where('table_to_cache', 'leaderboard');

    $max_cache_number = $query->get();
    $max_cache_number = json_decode(json_encode($max_cache_number),true);


    $query = DB::table('heroesprofile_cache.leaderboard');
    $query->where('game_type', $this->game_type);
    $query->where('season', $this->season);
    $query->where('type', $mmr_id);
    $query->where('cache_number', $max_cache_number);
    if($this->region != ""){
      $query->where('region', $this->region);
    }
    $query->limit(250);
    $data = $query->get();
    $data = json_decode(json_encode($data),true);

    $return_data = array();
    for($i = 0; $i < count($data); $i++){
      $leaderboard_data = array();
      $leaderboard_data["split_battletag"] = $data[$i]["split_battletag"];
      $leaderboard_data["blizz_id"] = $data[$i]["blizz_id"];
      $leaderboard_data["region"] = $data[$i]["region"];
      $leaderboard_data["win_rate"] = $data[$i]["win_rate"];
      $leaderboard_data["win"] = $data[$i]["win"];
      $leaderboard_data["loss"] = $data[$i]["loss"];
      $leaderboard_data["games_played"] = $data[$i]["games_played"];
      $leaderboard_data["conservative_rating"] = $data[$i]["conservative_rating"];
      $leaderboard_data["rating"] = $data[$i]["rating"];

      if(!array_key_exists($data[$i]["blizz_id"] . $data[$i]["region"], $return_data)){
        $return_data[$data[$i]["blizz_id"] . $data[$i]["region"]] = $leaderboard_data;
      }
    }
    return $return_data;
  }
}
