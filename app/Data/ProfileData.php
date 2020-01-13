<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;
use Cache;
use Session;
use App\Battletag;
use App\LeagueBreakdown;
use App\LeagueTier;
use DateTime;

class ProfileData
{
  private $full_battletag;
  private $blizz_id;
  private $region;
  private $game_type;
  private $season;

  public static function instance($full_battletag, $blizz_id, $region, $game_type, $season)
  {
      return new ProfileData($full_battletag, $blizz_id, $region, $game_type, $season);
  }


  public function __construct($full_battletag, $blizz_id, $region, $game_type, $season) {
    $this->full_battletag = $full_battletag;
    $this->blizz_id = $blizz_id;
    $this->region = $region;
    $this->game_type = $game_type;
    $this->season = $season;

  }

  public function getPlayerProfileData(){
    $return_data = Cache::rememberForever("PlayerProfile" . "|" . $this->blizz_id . "|" . $this->region . "|" . $this->season . "|" . $this->game_type, function (){
      $wins = 0;
      $losses = 0;
      $kills = 0;
      $takedowns = 0;
      $deaths = 0;
      $account_level = 0;

      $total_game_time = 0;

      $first_to_ten_wins = 0;
      $first_to_ten_losses = 0;

      $second_to_ten_wins = 0;
      $second_to_ten_losses = 0;

      $heros_played = array();
      foreach (Session::get("heroes_by_id") as $key => $value){
        $heros_played[$key]["wins"] = 0;
        $heros_played[$key]["losses"] = 0;
        $heros_played[$key]["latest_game"] = "2000-12-10 19:14:28";
      }

      $roles_played = array();
      foreach (Session::get("role_names") as $key => $value){
        $roles_played[$key]["wins"] = 0;
        $roles_played[$key]["losses"] = 0;
      }

      $game_type_played = array();

      for($i = 1; $i <= 5; $i++){
        $game_type_played[$i]["wins"] = 0;
        $game_type_played[$i]["losses"] = 0;
        $game_type_played[$i]["mmr"] = 0;
      }

      $maps_played = array();
      foreach (Session::get("maps_by_id") as $key => $value){
        $maps_played[$key]["wins"] = 0;
        $maps_played[$key]["losses"] = 0;
        $maps_played[$key]["latest_game"] = "2000-12-10 19:14:28";
      }
      $latest_replayID = 0;


      $replay_data = $this->grabProfileReplayData();
      $replay_data = json_decode(json_encode($replay_data),true);

      $replay_count = count($replay_data);
      $matches = array();
      $player_mmr_parsed_data = array();
      $hero_mmr_parsed_data = array();
      $matches_counter = 0;

      foreach ($replay_data as $replayID => $value){
        $account_level = $replay_data[$replayID]["account_level"];

        if($replay_data[$replayID]["winner"] == 1){
          $wins++;
          $heros_played[$replay_data[$replayID]["hero"]]["wins"]++;
          $maps_played[$replay_data[$replayID]["game_map"]]["wins"]++;
          $roles_played[$replay_data[$replayID]["role"]]["wins"]++;

          if($replay_data[$replayID]["first_to_ten"] == 1){
            $first_to_ten_wins++;
          }else if($replay_data[$replayID]["first_to_ten"] == "0"){
            $second_to_ten_wins++;
          }

          $game_type_played[$replay_data[$replayID]["game_type"]]["wins"]++;
        }else{
          $losses++;
          $heros_played[$replay_data[$replayID]["hero"]]["losses"]++;
          $maps_played[$replay_data[$replayID]["game_map"]]["losses"]++;
          $roles_played[$replay_data[$replayID]["role"]]["losses"]++;

          if($replay_data[$replayID]["first_to_ten"] == 1){
            $first_to_ten_losses++;
          }else if($replay_data[$replayID]["first_to_ten"] == "0"){
            $second_to_ten_losses++;
          }

          $game_type_played[$replay_data[$replayID]["game_type"]]["losses"]++;
        }

        $game_type_played[$replay_data[$replayID]["game_type"]]["mmr"] = $replay_data[$replayID]["player_current_mmr"];

        $old_hero_game_date = new DateTime($heros_played[$replay_data[$replayID]["hero"]]["latest_game"]);
        $old_game_map_game_date = new DateTime($maps_played[$replay_data[$replayID]["game_map"]]["latest_game"]);
        $new_hero_game_date = new DateTime($replay_data[$replayID]["game_date"]);

        if($old_hero_game_date < $new_hero_game_date){
          $heros_played[$replay_data[$replayID]["hero"]]["latest_game"] = $replay_data[$replayID]["game_date"];
        }

        if($old_game_map_game_date < $new_hero_game_date){
          $maps_played[$replay_data[$replayID]["game_map"]]["latest_game"] = $replay_data[$replayID]["game_date"];
        }

        $takedowns += $replay_data[$replayID]["takedowns"];
        $kills += $replay_data[$replayID]["kills"];
        $deaths += $replay_data[$replayID]["deaths"];
        $total_game_time += $replay_data[$replayID]["game_length"];

        if($latest_replayID < $replayID){
          $latest_replayID = $replayID;
        }

        $replay_count--;

        if($replay_count < 10){
          $matches[$matches_counter]["replayID"] = $replayID;
          $matches[$matches_counter]["hero"] = $replay_data[$replayID]["hero"];
          $matches[$matches_counter]["game_type"] = $replay_data[$replayID]["game_type"];
          $matches[$matches_counter]["game_date"] = $replay_data[$replayID]["game_date"];
          $matches[$matches_counter]["winner"] = $replay_data[$replayID]["winner"];
          $matches[$matches_counter]["player_conservative_rating"] = $replay_data[$replayID]["player_conservative_rating"];
          $matches[$matches_counter]["hero_conservative_rating"] = $replay_data[$replayID]["hero_conservative_rating"];
          $matches[$matches_counter]["game_map"] = $replay_data[$replayID]["game_map"];

          $talents = array();
          $talents["level_one"] = $replay_data[$replayID]["level_one"];
          $talents["level_four"] = $replay_data[$replayID]["level_four"];
          $talents["level_seven"] = $replay_data[$replayID]["level_seven"];
          $talents["level_ten"] = $replay_data[$replayID]["level_ten"];
          $talents["level_thirteen"] = $replay_data[$replayID]["level_thirteen"];
          $talents["level_sixteen"] = $replay_data[$replayID]["level_sixteen"];
          $talents["level_twenty"] = $replay_data[$replayID]["level_twenty"];

          $matches[$matches_counter]["talents"] = $talents;
          $matches_counter++;
        }


        $data = array();
        $data["mmr_date_parsed"] = $replay_data[$replayID]["mmr_date_parsed"];
        $data["player_conservative_rating"] = $replay_data[$replayID]["player_conservative_rating"];
        $player_mmr_parsed_data[$replay_data[$replayID]["game_type"]][$replayID] = $data;

        $data = array();
        $data["mmr_date_parsed"] = $replay_data[$replayID]["mmr_date_parsed"];
        $data["hero_conservative_rating"] = $replay_data[$replayID]["hero_conservative_rating"];
        $hero_mmr_parsed_data[$replay_data[$replayID]["game_type"]][$replay_data[$replayID]["hero"]][$replayID] = $data;
      }

      /*
      for($i = 1; $i <= 5; $i++){
        $heros_played = $this->getHeroPlayedMMR($heros_played, $i);
      }
      */

      $return_data = array();
      $return_data["wins"] = $wins;
      $return_data["losses"] = $losses;
      $return_data["kills"] = $kills;
      $return_data["takedowns"] = $takedowns;
      $return_data["deaths"] = $deaths;
      $return_data["account_level"] = $account_level;
      $return_data["total_game_time"] = $total_game_time;
      $return_data["first_to_ten_wins"] = $first_to_ten_wins;
      $return_data["first_to_ten_losses"] = $first_to_ten_losses;
      $return_data["second_to_ten_wins"] = $second_to_ten_wins;
      $return_data["second_to_ten_losses"] = $second_to_ten_losses;



      $return_data["hero_data"] = $heros_played;
      $return_data["role_data"] = $roles_played;
      $return_data["game_type_data"] = $game_type_played;
      $return_data["game_map_data"] = $maps_played;
      $return_data["latest_replayID"] = $latest_replayID;

      for($i = 1; $i <= 5; $i++){
        if(array_key_exists($i, $player_mmr_parsed_data)){
          $player_mmr_parsed_data[$i] = \GlobalFunctions::instance()->sortKeyValueArray($player_mmr_parsed_data[$i], "mmr_parsed_sorted_desc");
        }
      }

      for($i = 1; $i <= 5; $i++){
        if(array_key_exists($i, $hero_mmr_parsed_data)){
          foreach (Session::get("heroes_by_id") as $key => $value){
            if(array_key_exists($key, $hero_mmr_parsed_data[$i])){
              $hero_mmr_parsed_data[$i][$key] = \GlobalFunctions::instance()->sortKeyValueArray($hero_mmr_parsed_data[$i][$key], "mmr_parsed_sorted_desc");
            }
          }
        }
      }
      $return_data["player_mmr_parsed_data"] = $player_mmr_parsed_data;
      $return_data["hero_mmr_parsed_data"] = $hero_mmr_parsed_data;

      for($i = (count($matches) - 1); $i >= 0; $i--){
        $mmr = $matches[$i]["player_conservative_rating"];
        $hero_mmr = $matches[$i]["hero_conservative_rating"];
        $game_type = $matches[$i]["game_type"];
        $replayID = $matches[$i]["replayID"];
        $hero = $matches[$i]["hero"];

        $player_next = 0;
        foreach ($return_data["player_mmr_parsed_data"][$game_type] as $mmr_replayID => $mmr_data){
          if($player_next){
            $matches[$i]["player_mmr_change"] = (1800 + 40 * $mmr) - (1800 + 40 * $mmr_data["player_conservative_rating"]);
            break;
          }
          if($mmr_replayID == $replayID){
            $player_next = 1;
          }
        }


        $hero_next = 0;
        foreach ($return_data["hero_mmr_parsed_data"][$game_type][$hero] as $mmr_replayID => $mmr_data){
          if($hero_next){
            $matches[$i]["hero_mmr_change"] = (1800 + 40 * $hero_mmr) - (1800 + 40 * $mmr_data["hero_conservative_rating"]);
            break;
          }
          if($mmr_replayID == $replayID){
            $hero_next = 1;
          }
        }
      }



      $return_data["matches_data"] = $matches;


      return $return_data;
    });

    $return_data_check = $this->checkForNewReplays($return_data);
    echo "New Replays: " . $return_data_check[0] . "<br>";

    if($return_data_check[0]){
      $return_data = $return_data_check[1];
      echo "Extra data" . "<br>";
      //Cache::forever("PlayerProfile" . "|" . $this->blizz_id . "|" . $this->region . "|" . $this->season . "|" . $this->game_type, $return_data);
    }

    $return_data["time_played"] = \GlobalFunctions::instance()->secondsToTime($return_data["total_game_time"]);
    return $return_data;
  }

  private function grabProfileReplayData(){
    $roles_by_hero_name = Session::get('roles_by_hero_name');
    $heroes_by_id = Session::get('heroes_by_id');

    $query = DB::table('heroesprofile.replay')
    ->select(
      'replay.replayID',
      'replay.game_type',
      'replay.game_date',
      'replay.game_length',
      'replay.game_map',
      'player.hero',
      'player.winner',
      'player.player_conservative_rating',
      'player.mmr_date_parsed',
      'master_mmr_data.conservative_rating as player_current_mmr',
      'player.hero_conservative_rating',
      'battletags.account_level',
      'scores.kills',
      'scores.takedowns',
      'scores.deaths',
      'scores.first_to_ten',
      'talents.level_one',
      'talents.level_four',
      'talents.level_seven',
      'talents.level_ten',
      'talents.level_thirteen',
      'talents.level_sixteen',
      'talents.level_twenty'
    );

    $query->join('heroesprofile.player', 'heroesprofile.player.replayID', '=', 'heroesprofile.replay.replayID');
    $query->join('heroesprofile.battletags', 'heroesprofile.battletags.player_id', '=', 'heroesprofile.player.battletag');

    $query->join('heroesprofile.master_mmr_data', function($join)
      {
        $join->where('heroesprofile.master_mmr_data.type_value', '=', 10000);
        $join->on('heroesprofile.master_mmr_data.game_type', '=', 'heroesprofile.replay.game_type');
        $join->where('heroesprofile.master_mmr_data.region', '=', $this->region);
        $join->where('heroesprofile.master_mmr_data.blizz_id', '=', $this->blizz_id);
      }
    );


    $query->join('heroesprofile.scores', function($join)
      {
        $join->on('heroesprofile.scores.replayID', '=', 'heroesprofile.replay.replayID');
        $join->on('heroesprofile.scores.battletag', '=', 'heroesprofile.player.battletag');
      }
    );
    $query->join('heroesprofile.talents', function($join)
      {
        $join->on('heroesprofile.talents.replayID', '=', 'heroesprofile.replay.replayID');
        $join->on('heroesprofile.talents.battletag', '=', 'heroesprofile.player.battletag');
      }
    );

    //$query->where('replay.replayID', '<', 21909038);

    $query->where('replay.region', $this->region);
    $query->where('player.blizz_id', $this->blizz_id);
    $query->orderBy('replay.game_date', 'ASC');
    //$query->limit('4200');
    /*
    print_r($query->toSql());
    print_r($query->getBindings());
    echo "<br>";
    echo "<br>";
    */
    $data = $query->get();
    $data = json_decode(json_encode($data),true);
    $returnData = array();

    for($i = 0; $i < count($data); $i++){
      $data[$i]["role"] = $roles_by_hero_name[$heroes_by_id[$data[$i]["hero"]]];
      //$data[$i]["season"] = \GlobalFunctions::instance()->getSeason($data[$i]["game_date"]);
      $returnData[$data[$i]["replayID"]] = $data[$i];
    }
    return $returnData;
  }

  private function getHeroPlayedMMR($heros_played, $game_type){
    $sql = "SELECT type_value, conservative_rating FROM heroesprofile.master_mmr_data where type_value in (SELECT id FROM heroesprofile.heroes) and game_type = " . $game_type . " and blizz_id = " . $this->blizz_id . " and region = " . $this->region;
    $data = DB::connection('mysql')->select($sql);
    $data = json_decode(json_encode($data),true);

    for($i = 0; $i < count($data); $i++){
      $type_value = $data[$i]["type_value"];
      $conservative_rating = $data[$i]["conservative_rating"];
      $heros_played[$type_value]["mmr_data"][$game_type] = $conservative_rating;
    }
    return $heros_played;
  }

  private function checkForNewReplays($return_data){
    $roles_by_hero_name = Session::get('roles_by_hero_name');
    $heroes_by_id = Session::get('heroes_by_id');

    $latest_replay = $return_data['latest_replayID'];


    $query = DB::table('heroesprofile.replay')
    ->select(
      'replay.replayID',
      'replay.game_type',
      'replay.game_date',
      'replay.game_length',
      'replay.game_map',
      'player.hero',
      'player.winner',
      'player.player_conservative_rating',
      'player.mmr_date_parsed',
      'master_mmr_data.conservative_rating as player_current_mmr',
      'player.hero_conservative_rating',
      'battletags.account_level',
      'scores.kills',
      'scores.takedowns',
      'scores.deaths',
      'scores.first_to_ten',
      'talents.level_one',
      'talents.level_four',
      'talents.level_seven',
      'talents.level_ten',
      'talents.level_thirteen',
      'talents.level_sixteen',
      'talents.level_twenty'
    );

    $query->join('heroesprofile.player', 'heroesprofile.player.replayID', '=', 'heroesprofile.replay.replayID');
    $query->join('heroesprofile.battletags', 'heroesprofile.battletags.player_id', '=', 'heroesprofile.player.battletag');

    $query->join('heroesprofile.master_mmr_data', function($join)
      {
        $join->where('heroesprofile.master_mmr_data.type_value', '=', 10000);
        $join->on('heroesprofile.master_mmr_data.game_type', '=', 'heroesprofile.replay.game_type');
        $join->where('heroesprofile.master_mmr_data.region', '=', $this->region);
        $join->where('heroesprofile.master_mmr_data.blizz_id', '=', $this->blizz_id);
      }
    );


    $query->join('heroesprofile.scores', function($join)
      {
        $join->on('heroesprofile.scores.replayID', '=', 'heroesprofile.replay.replayID');
        $join->on('heroesprofile.scores.battletag', '=', 'heroesprofile.player.battletag');
      }
    );
    $query->join('heroesprofile.talents', function($join)
      {
        $join->on('heroesprofile.talents.replayID', '=', 'heroesprofile.replay.replayID');
        $join->on('heroesprofile.talents.battletag', '=', 'heroesprofile.player.battletag');
      }
    );

    $query->where('replay.replayID', '>', $latest_replay);

    $query->where('replay.region', $this->region);
    $query->where('player.blizz_id', $this->blizz_id);
    $query->orderBy('replay.game_date', 'ASC');
    //$query->limit('4200');

    $data = $query->get();
    //print_r($query->toSql());
    //print_r($query->getBindings());
    //echo "<br>";
    //echo "<br>";
    $data = json_decode(json_encode($data),true);

    $replay_data = array();
    for($i = 0; $i < count($data); $i++){
      $data[$i]["role"] = $roles_by_hero_name[$heroes_by_id[$data[$i]["hero"]]];
      //$data[$i]["season"] = \GlobalFunctions::instance()->getSeason($data[$i]["game_date"]);
      $replay_data[$data[$i]["replayID"]] = $data[$i];
    }
    $matches = array();
    $replay_count = count($replay_data);
    $matches_counter = 0;
    foreach ($replay_data as $replayID => $value){
      $return_data["account_level"] = $replay_data[$replayID]["account_level"];
      $replay_data[$replayID]["role"] = $roles_by_hero_name[$heroes_by_id[$replay_data[$replayID]["hero"]]];
      if($replay_data[$replayID]["winner"] == 1){
        $return_data["wins"]++;
        $return_data["hero_data"][$replay_data[$replayID]["hero"]]["wins"]++;
        $return_data["game_map_data"][$replay_data[$replayID]["game_map"]]["wins"]++;
        $return_data["role_data"][$replay_data[$replayID]["role"]]["wins"]++;
        if($replay_data[$replayID]["first_to_ten"] == 1){
          $return_data["first_to_ten_wins"]++;
        }else{
          $return_data["second_to_ten_wins"]++;
        }
        $return_data["game_type_data"][$replay_data[$replayID]["game_type"]]["wins"]++;
      }else{
        $return_data["losses"]++;
        $return_data["hero_data"][$replay_data[$replayID]["hero"]]["losses"]++;
        $return_data["game_map_data"][$replay_data[$replayID]["game_map"]]["losses"]++;
        $return_data["role_data"][$replay_data[$replayID]["role"]]["losses"]++;
        if($replay_data[$replayID]["first_to_ten"] == 1){
          $return_data["first_to_ten_losses"]++;
        }else{
          $return_data["second_to_ten_losses"]++;
        }
        $return_data["game_type_data"][$replay_data[$replayID]["game_type"]]["losses"]++;
      }
      $return_data["game_type_data"][$replay_data[$replayID]["game_type"]]["mmr"] = $replay_data[$replayID]["player_current_mmr"];
      $old_hero_game_date = new DateTime($return_data["hero_data"][$replay_data[$replayID]["hero"]]["latest_game"]);
      $old_game_map_game_date = new DateTime($return_data["game_map_data"][$replay_data[$replayID]["game_map"]]["latest_game"]);
      $new_hero_game_date = new DateTime($replay_data[$replayID]["game_date"]);
      if($old_hero_game_date < $new_hero_game_date){
        $return_data["hero_data"][$replay_data[$replayID]["hero"]]["latest_game"] = $replay_data[$replayID]["game_date"];
      }
      if($old_game_map_game_date < $new_hero_game_date){
        $return_data["game_map_data"][$replay_data[$replayID]["game_map"]]["latest_game"] = $replay_data[$replayID]["game_date"];
      }
      $return_data["takedowns"] += $replay_data[$replayID]["takedowns"];
      $return_data["kills"] += $replay_data[$replayID]["kills"];
      $return_data["deaths"] += $replay_data[$replayID]["deaths"];
      $return_data["total_game_time"] += $replay_data[$replayID]["game_length"];
      if($return_data["latest_replayID"] < $replayID){
        $return_data["latest_replayID"] = $replayID;
      }
      $replay_count--;
      if($replay_count < 10){
        $matches[$matches_counter]["replayID"] = $replayID;
        $matches[$matches_counter]["hero"] = $replay_data[$replayID]["hero"];
        $matches[$matches_counter]["game_type"] = $replay_data[$replayID]["game_type"];
        $matches[$matches_counter]["game_date"] = $replay_data[$replayID]["game_date"];
        $matches[$matches_counter]["winner"] = $replay_data[$replayID]["winner"];
        $matches[$matches_counter]["player_conservative_rating"] = $replay_data[$replayID]["player_conservative_rating"];
        $matches[$matches_counter]["hero_conservative_rating"] = $replay_data[$replayID]["hero_conservative_rating"];
        $matches[$matches_counter]["game_map"] = $replay_data[$replayID]["game_map"];
        $talents = array();
        $talents["level_one"] = $replay_data[$replayID]["level_one"];
        $talents["level_four"] = $replay_data[$replayID]["level_four"];
        $talents["level_seven"] = $replay_data[$replayID]["level_seven"];
        $talents["level_ten"] = $replay_data[$replayID]["level_ten"];
        $talents["level_thirteen"] = $replay_data[$replayID]["level_thirteen"];
        $talents["level_sixteen"] = $replay_data[$replayID]["level_sixteen"];
        $talents["level_twenty"] = $replay_data[$replayID]["level_twenty"];

        $matches[$matches_counter]["talents"] = $talents;
      }
      $matches_counter++;
      $data = array();
      $data["mmr_date_parsed"] = $replay_data[$replayID]["mmr_date_parsed"];
      $data["player_conservative_rating"] = $replay_data[$replayID]["player_conservative_rating"];
      $return_data["player_mmr_parsed_data"][$replay_data[$replayID]["game_type"]][$replayID] = $data;

      $data = array();
      $data["mmr_date_parsed"] = $replay_data[$replayID]["mmr_date_parsed"];
      $data["hero_conservative_rating"] = $replay_data[$replayID]["hero_conservative_rating"];
      $return_data["hero_mmr_parsed_data"][$replay_data[$replayID]["game_type"]][$replay_data[$replayID]["hero"]][$replayID] = $data;
    }

    $temp_array = \GlobalFunctions::instance()->sortKeyValueArray(array_merge($return_data["matches_data"], $matches), "cmp_game_date_asc");
    $counter_of_array =  count($temp_array);
    $temp_array = array_slice($temp_array, ($counter_of_array - 10), 10);




    for($i = (count($temp_array) - 1); $i >= 0; $i--){
      $mmr = $temp_array[$i]["player_conservative_rating"];
      $hero_mmr = $temp_array[$i]["hero_conservative_rating"];
      $game_type = $temp_array[$i]["game_type"];
      $replayID = $temp_array[$i]["replayID"];
      $hero = $temp_array[$i]["hero"];

      $player_next = 0;
      foreach ($return_data["player_mmr_parsed_data"][$game_type] as $mmr_replayID => $mmr_data){
        if($player_next){
          $temp_array[$i]["player_mmr_change"] = (1800 + 40 * $mmr) - (1800 + 40 * $mmr_data["player_conservative_rating"]);
          break;
        }
        if($mmr_replayID == $replayID){
          $player_next = 1;
        }
      }


      $hero_next = 0;
      foreach ($return_data["hero_mmr_parsed_data"][$game_type][$hero] as $mmr_replayID => $mmr_data){
        if($hero_next){
          $temp_array[$i]["hero_mmr_change"] = (1800 + 40 * $hero_mmr) - (1800 + 40 * $mmr_data["hero_conservative_rating"]);
          break;
        }
        if($mmr_replayID == $replayID){
          $hero_next = 1;
        }
      }
    }



    $return_data["matches_data"] = $temp_array;


    for($i = 1; $i <= 5; $i++){
      if(array_key_exists($i, $return_data["player_mmr_parsed_data"])){
        $return_data["player_mmr_parsed_data"][$i] = \GlobalFunctions::instance()->sortKeyValueArray($return_data["player_mmr_parsed_data"][$i], "mmr_parsed_sorted_desc");
      }
    }

    for($i = 1; $i <= 5; $i++){
      if(array_key_exists($i, $return_data["hero_mmr_parsed_data"])){
        foreach (Session::get("heroes_by_id") as $key => $value){
          if(array_key_exists($key, $return_data["hero_mmr_parsed_data"][$i])){
            $return_data["hero_mmr_parsed_data"][$i][$key] = \GlobalFunctions::instance()->sortKeyValueArray($return_data["hero_mmr_parsed_data"][$i][$key], "mmr_parsed_sorted_desc");
          }
        }
      }
    }

    if(count($replay_data) > 0){
      return array(1, $return_data);
    }else{
      return array(0, $return_data);
    }
  }

  public function getPlayerFriendsAndFoes(){
    $return_data = Cache::rememberForever("FriendsAndFoes" . "|" . $this->blizz_id . "|" . $this->region . "|" . $this->season . "|" . $this->game_type, function (){
      $return_data = $this->grabFriendsAndFoesReplayData();
      return $return_data;
    });

    return $return_data;
  }

  private function grabFriendsAndFoesReplayData(){
    $sql = "SELECT " .
    "replay.replayID, " .
    "player.blizz_id, " .
    "replay.region, " .
    "replay.game_type, " .
    "replay.game_map, " .
    "player.hero, " .
    "player.winner, " .
    "player.team " .
    "FROM replay " .
    "inner join player on player.replayID = replay.replayID " .
    "where replay.replayID in (select replay.replayID from replay inner join player on player.replayID = replay.replayID where blizz_id = " . $this->blizz_id . " and region = ". $this->region . ")";
    $db_data = DB::connection('mysql')->select($sql);
    $db_data = json_decode(json_encode($db_data),true);

    $sql = "SELECT " .
    "blizz_id, " .
    "battletag, " .
    "latest_game " .
    "FROM heroesprofile.battletags " .
    "where blizz_id in (SELECT " .
    "DISTINCT(player.blizz_id) "  .
    "FROM replay INNER JOIN player ON player.replayID = replay.replayID " .
    "WHERE " .
    "replay.replayID IN (SELECT " .
    "replay.replayID " .
    "FROM replay INNER JOIN player ON player.replayID = replay.replayID " .
    "WHERE " .
    "blizz_id = " . $this->blizz_id . " AND region = " . $this->region . ")) and region = " . $this->region;
    $b_data = DB::connection('mysql')->select($sql);
    $b_data = json_decode(json_encode($b_data),true);

    $battletag_data = array();
    $battletags_latest = array();

    for($i = 0; $i < count($b_data); $i++){


      if(array_key_exists($b_data[$i]["blizz_id"], $battletags_latest)){
        if($battletags_latest[$b_data[$i]["blizz_id"]] < $b_data[$i]["latest_game"]){
          $battletag_data[$b_data[$i]["blizz_id"]] = $b_data[$i]["battletag"];
        }
      }else{
        $battletags_latest[$b_data[$i]["blizz_id"]] = $b_data[$i]["latest_game"];
        $battletag_data[$b_data[$i]["blizz_id"]] = $b_data[$i]["battletag"];
      }
    }

    $return_data = array();
    $finalData = array();
    $prevReplay = "";

    for($i = 0; $i < count($db_data); $i++){
      if($prevReplay != "" && $prevReplay != $db_data[$i]["replayID"]){
        $finalData[$prevReplay] = $return_data;
        $return_data = array();

      }
      $return_data[$db_data[$i]["blizz_id"]]["battletag"] = $battletag_data[$db_data[$i]["blizz_id"]];
      $return_data[$db_data[$i]["blizz_id"]]["blizz_id"] = $db_data[$i]["blizz_id"];
      $return_data[$db_data[$i]["blizz_id"]]["hero"] = Session::get("heroes_by_id")[$db_data[$i]["hero"]];
      $return_data[$db_data[$i]["blizz_id"]]["team"] = $db_data[$i]["team"];
      $return_data[$db_data[$i]["blizz_id"]]["winner"] = $db_data[$i]["winner"];
      $prevReplay = $db_data[$i]["replayID"];

    }
    $finalData[$prevReplay] = $return_data;

    $allies = array();
    $enemies = array();

    foreach ($finalData as $replayID => $value){
      foreach ($value as $blizz_id => $data){
        //echo $value[$blizz_id]["battletag"];
        //echo "<br />\n";

        if($blizz_id != $this->blizz_id){
          if($value[$blizz_id]["team"] == $finalData[$replayID][$this->blizz_id]["team"]){
            if(array_key_exists($value[$blizz_id]["blizz_id"], $allies)){
              $allies[$value[$blizz_id]["blizz_id"]]["games_played"]++;




              if(!array_key_exists($value[$blizz_id]["hero"], $allies[$value[$blizz_id]["blizz_id"]]["hero"])){
                $allies[$value[$blizz_id]["blizz_id"]]["hero"][$value[$blizz_id]["hero"]] = 0;
              }
              $allies[$value[$blizz_id]["blizz_id"]]["hero"][$value[$blizz_id]["hero"]]++;


              if($value[$blizz_id]["winner"] == "1"){
                $allies[$value[$blizz_id]["blizz_id"]]["wins"]++;

              }else{
                $allies[$value[$blizz_id]["blizz_id"]]["losses"]++;

              }
            }else{
              $allies[$value[$blizz_id]["blizz_id"]]["games_played"] = 1;


              if($value[$blizz_id]["winner"] == "1"){
                $allies[$value[$blizz_id]["blizz_id"]]["wins"] = 1;
                $allies[$value[$blizz_id]["blizz_id"]]["losses"] = 0;

              }else{
                $allies[$value[$blizz_id]["blizz_id"]]["losses"] = 1;
                $allies[$value[$blizz_id]["blizz_id"]]["wins"] = 0;
              }


              $allies[$value[$blizz_id]["blizz_id"]]["hero"][$value[$blizz_id]["hero"]] = 1;


            }

          }else{
            if(array_key_exists($value[$blizz_id]["blizz_id"], $enemies)){
              $enemies[$value[$blizz_id]["blizz_id"]]["games_played"]++;




              if(!array_key_exists($value[$blizz_id]["hero"], $enemies[$value[$blizz_id]["blizz_id"]]["hero"])){
                $enemies[$value[$blizz_id]["blizz_id"]]["hero"][$value[$blizz_id]["hero"]] = 0;
              }
              $enemies[$value[$blizz_id]["blizz_id"]]["hero"][$value[$blizz_id]["hero"]]++;


              if($value[$blizz_id]["winner"] == "1"){
                $enemies[$value[$blizz_id]["blizz_id"]]["wins"]++;

              }else{
                $enemies[$value[$blizz_id]["blizz_id"]]["losses"]++;

              }
            }else{
              $enemies[$value[$blizz_id]["blizz_id"]]["games_played"] = 1;


              if($value[$blizz_id]["winner"] == "1"){
                $enemies[$value[$blizz_id]["blizz_id"]]["wins"] = 1;
                $enemies[$value[$blizz_id]["blizz_id"]]["losses"] = 0;

              }else{
                $enemies[$value[$blizz_id]["blizz_id"]]["losses"] = 1;
                $enemies[$value[$blizz_id]["blizz_id"]]["wins"] = 0;
              }


              $enemies[$value[$blizz_id]["blizz_id"]]["hero"][$value[$blizz_id]["hero"]] = 1;


            }
          }
        }


      }

    }
    arsort($allies);
    arsort($enemies);
    //print_r($allies["67280"]);
    $return_allies = array();
    $return_enemies = array();

    //$a_i = 0;
    //$e_i = 0;

    //Change to be dynamic as a view more without having to run this code over again.
    foreach ($allies as $key => $value){
      //if($a_i == 50){break;}
      $return_allies[$key] = $value;
      //$a_i++;
    }

    foreach ($enemies as $key => $value){
      //if($e_i == 50){break;}
      $return_enemies[$key] = $value;
      //$e_i++;
    }

    return (array($return_allies, $return_enemies, $battletags));
  }

  public function grabAllHeroData(){
    $return_data = Cache::rememberForever("HeroesAll" . "|" . $this->blizz_id . "|" . $this->region . "|" . $this->season . "|" . $this->game_type, function (){
      $return_data = $this->getAllHeroReplayData();
      $return_data = $this->getAllHeroMMRData($return_data);
      return $return_data;
    });

    return $return_data;
  }

  private function getAllHeroReplayData(){
    $query = DB::table('heroesprofile.replay');
    $query->join('heroesprofile.player', 'heroesprofile.player.replayID', '=', 'heroesprofile.replay.replayID');
    $query->where('heroesprofile.replay.region', $this->region);
    $query->where('heroesprofile.player.blizz_id', $this->blizz_id);
    $query->select(
        'heroesprofile.replay.game_type',
        'heroesprofile.replay.game_map',
        'heroesprofile.player.hero',
        'heroesprofile.player.winner',
        DB::raw('COUNT(*) as total_games')
      );
    $query->groupBy('game_type', 'game_map', 'hero', 'winner');
    $data = $query->get();
    $data = json_decode(json_encode($data),true);
    //print_r($query->toSql());
    //echo "<br>";
    //print_r($query->getBindings());

    $return_data = array();
    for($i = 0; $i < count($data); $i++){
      $hero_name = Session::get("heroes_by_id")[$data[$i]["hero"]];
      $game_type_name = Session::get("game_types_by_id")[$data[$i]["game_type"]];
      $game_map_name = Session::get("maps_by_id")[$data[$i]["game_map"]];

      if($data[$i]["winner"] == 1){
        $return_data[$hero_name]["game_data"][$game_type_name][$game_map_name]["wins"] = $data[$i]["total_games"];
      }else{
        $return_data[$hero_name]["game_data"][$game_type_name][$game_map_name]["losses"] = $data[$i]["total_games"];
      }
    }
    return $return_data;
  }

  private function getAllHeroMMRData($return_data){
    $query = DB::table('heroesprofile.master_mmr_data');
    $query->whereIn('heroesprofile.master_mmr_data.type_value', json_decode(json_encode(DB::table('heroesprofile.heroes')->select('id')->get()),true));
    $query->whereIn('heroesprofile.master_mmr_data.game_type', json_decode(json_encode(DB::table('heroesprofile.game_types')->select('type_id')->get()),true));
    $query->where('heroesprofile.master_mmr_data.blizz_id', $this->blizz_id);
    $query->where('heroesprofile.master_mmr_data.region', $this->region);
    $data = $query->get();
    $data = json_decode(json_encode($data),true);



    for($i = 0; $i < count($data); $i++){
      $hero_name = Session::get("heroes_by_id")[$data[$i]["type_value"]];
      $game_type_name = Session::get("game_types_by_id")[$data[$i]["game_type"]];

      $return_data[$hero_name]["mmr_data"][$game_type_name] = 1800 + 40 * $data[$i]["conservative_rating"];
    }
    /*
    print_r($query->toSql());
    echo "<br>";
    print_r($query->getBindings());
    */

    return $return_data;
  }

  public function grabSingleHeroData($hero){
    $return_data = Cache::rememberForever("HeroesSingle" . "|" . $hero . "|" . $this->blizz_id . "|" . $this->region . "|" . $this->season . "|" . $this->game_type, function () use ($hero){
      $return_data = $this->getSingleHeroReplayData($hero);
      return $return_data;
    });

    return $return_data;

  }

  private function getSingleHeroReplayData($hero){
    $query = DB::table('heroesprofile.replay');
    $query->join('heroesprofile.player', 'heroesprofile.player.replayID', '=', 'heroesprofile.replay.replayID');
    $query->join('heroesprofile.scores', function($join)
      {
        $join->on('heroesprofile.scores.replayID', '=', 'heroesprofile.replay.replayID');
        $join->on('heroesprofile.scores.battletag', '=', 'heroesprofile.player.battletag');
      }
    );
    $query->join('heroesprofile.talents', function($join)
      {
        $join->on('heroesprofile.talents.replayID', '=', 'heroesprofile.replay.replayID');
        $join->on('heroesprofile.talents.battletag', '=', 'heroesprofile.player.battletag');
      }
    );
    $query->where('heroesprofile.replay.region', $this->region);
    $query->where('heroesprofile.player.blizz_id', $this->blizz_id);
    $query->where('heroesprofile.player.hero', $hero)->get()), true);

    $query->select(
        'heroesprofile.replay.game_type',
        'heroesprofile.replay.game_date',
        'heroesprofile.replay.game_length',
        'heroesprofile.replay.game_map',
        'heroesprofile.player.hero_level',
        'heroesprofile.player.mastery_taunt',
        'heroesprofile.player.winner',
        'heroesprofile.player.player_conservative_rating',
        'heroesprofile.player.hero_conservative_rating',
        'heroesprofile.player.role_conservative_rating',
        'heroesprofile.scores.kills',
        'heroesprofile.scores.assists',
        'heroesprofile.scores.takedowns',
        'heroesprofile.scores.deaths',
        'heroesprofile.scores.highest_kill_streak',
        'heroesprofile.scores.hero_damage',
        'heroesprofile.scores.siege_damage',
        'heroesprofile.scores.structure_damage',
        'heroesprofile.scores.minion_damage',
        'heroesprofile.scores.creep_damage',
        'heroesprofile.scores.summon_damage',
        //'heroesprofile.scores.time_cc_enemy_heroes',
        'heroesprofile.scores.healing',
        'heroesprofile.scores.self_healing',
        'heroesprofile.scores.damage_taken',
        'heroesprofile.scores.experience_contribution',
        'heroesprofile.scores.town_kills',
        'heroesprofile.scores.time_spent_dead',
        'heroesprofile.scores.merc_camp_captures',
        'heroesprofile.scores.watch_tower_captures',
        //'heroesprofile.scores.meta_experience',
        //'heroesprofile.scores.match_award',
        'heroesprofile.scores.protection_allies',
        'heroesprofile.scores.silencing_enemies',
        'heroesprofile.scores.rooting_enemies',
        'heroesprofile.scores.clutch_heals',
        'heroesprofile.scores.escapes',
        'heroesprofile.scores.vengeance',
        'heroesprofile.scores.outnumbered_deaths',
        'heroesprofile.scores.teamfight_escapes',
        'heroesprofile.scores.teamfight_healing',
        'heroesprofile.scores.teamfight_damage_taken',
        'heroesprofile.scores.teamfight_hero_damage',
        'heroesprofile.scores.multikill',
        'heroesprofile.scores.physical_damage',
        'heroesprofile.scores.spell_damage',
        'heroesprofile.scores.regen_globes',
        'heroesprofile.scores.first_to_ten',
        'heroesprofile.talents.level_one',
        'heroesprofile.talents.level_four',
        'heroesprofile.talents.level_seven',
        'heroesprofile.talents.level_ten',
        'heroesprofile.talents.level_thirteen',
        'heroesprofile.talents.level_sixteen',
        'heroesprofile.talents.level_twenty'
      );
    $data = $query->get();
    $data = json_decode(json_encode($data),true);

    $non_player_data = $this->getWinLossAllReplayDataSingleHero($hero);

    /*
    print_r($query->toSql());
    echo "<br>";
    print_r($query->getBindings());
    */


    $return_data = array();
    foreach (Session::get("season_dates") as $season => $season_data){
      foreach (Session::get("game_types_by_name") as $game_type => $game_type_data){
        $return_data[$season][$game_type]["wins"] = 0;
        $return_data[$season][$game_type]["losses"] = 0;
        $return_data[$season][$game_type]["takedowns"] = 0;
        $return_data[$season][$game_type]["kills"] = 0;
        $return_data[$season][$game_type]["assists"] = 0;
        $return_data[$season][$game_type]["deaths"] = 0;
      }
    }

    for($i = 0; $i < count($data); $i++){
      if($data[$i]["winner"] == 1){
        $return_data[$data[$i]["game_type"]]["wins"]++;
      }else{
        $return_data[$data[$i]["game_type"]]["losses"]++;
      }
      $return_data[$data[$i]["game_type"]]["takdowns"] += $data[$i]["takedowns"];
      $return_data[$data[$i]["game_type"]]["kills"] += $data[$i]["kills"];
      $return_data[$data[$i]["game_type"]]["assists"] += $data[$i]["assists"];
      $return_data[$data[$i]["game_type"]]["deaths"] += $data[$i]["deaths"];
    }



  }

  private function getWinLossAllReplayDataSingleHero($hero){
    $query = DB::table('heroesprofile.replay');
    $query->join('heroesprofile.player', 'heroesprofile.player.replayID', '=', 'heroesprofile.replay.replayID');
    $query->join('heroesprofile.scores', function($join)
      {
        $join->on('heroesprofile.scores.replayID', '=', 'heroesprofile.replay.replayID');
        $join->on('heroesprofile.scores.battletag', '=', 'heroesprofile.player.battletag');
      }
    );
    $query->join('heroesprofile.talents', function($join)
      {
        $join->on('heroesprofile.talents.replayID', '=', 'heroesprofile.replay.replayID');
        $join->on('heroesprofile.talents.battletag', '=', 'heroesprofile.player.battletag');
      }
    );

    $query->whereIn('replayID',
      json_decode(json_encode(DB::table('heroesprofile.replay')
        ->select('heroesprofile.replay.replayID')
        ->join('heroesprofile.player', 'heroesprofile.player.replayID', '=', 'heroesprofile.replay.replayID')
        ->where('heroesprofile.replay.region', $this->region)
        ->where('heroesprofile.player.blizz_id', $this->blizz_id)
        ->where('heroesprofile.player.hero', $hero)->get()), true)
    );
    $query->select(
        'heroesprofile.replay.game_type',
        'heroesprofile.replay.game_date',
        'heroesprofile.replay.game_map',
        'heroesprofile.replay.region',
        'heroesprofile.player.winner',
        'heroesprofile.player.hero',
        'heroesprofile.player.blizz_id'
      );
    $data = $query->get();
    return json_decode(json_encode($data),true);
  }
}
