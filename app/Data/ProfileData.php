<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;
use Session;
use App\Battletag;
use App\LeagueBreakdown;
use App\LeagueTier;
use DateTime;

class ProfileData
{
  private $blizz_id;
  private $region;

  private $player_data;
  private $player_data_mmr_sorted;

  private $mmr_data;

  public static function instance($blizz_id, $region)
  {
      return new ProfileData($blizz_id, $region);
  }


  public function __construct($blizz_id, $region) {
    $this->blizz_id = $blizz_id;
    $this->region = $region;

  }



  public function getPlayerData(){
    $query = DB::table('heroesprofile_cache.player_data');
    $query->where('region', $this->region);
    $query->where('blizz_id', $this->blizz_id);
    $cache_data = $query->get();
    $cache_data = json_decode(json_encode($cache_data),true);
    $found = false;
    if(count($cache_data) > 0){
      if($cache_data[0]["data"] == "null"){
        $this->grabAllReplays();
        $found = "true";
      }else{
        $this->player_data = json_decode($cache_data[0]["data"], true);
        $found = $this->checkForNewReplays();
      }
    }else{
      $this->grabAllReplays();
      $found = "true";
    }
    $this->player_data = \GlobalFunctions::instance()->sortKeyValueArray($this->player_data, "game_date_desc");
    $this->player_data_mmr_sorted = \GlobalFunctions::instance()->sortKeyValueArray($this->player_data, "mmr_parsed_sorted_asc");

    $player_prev_mmr = array();
    $player_prev_mmr[1] = 1800;
    $player_prev_mmr[2] = 1800;
    $player_prev_mmr[3] = 1800;
    $player_prev_mmr[4] = 1800;
    $player_prev_mmr[5] = 1800;

    $hero_prev_mmr = array();

    $role_prev_mmr= array();

    foreach (array_reverse($this->player_data_mmr_sorted, true) as $replayID => $data){
      $this->player_data_mmr_sorted[$replayID]["player_mmr_change"] = (1800 + 40 * $data["player_conservative_rating"]) - $player_prev_mmr[$data["game_type"]];
      $this->player_data[$replayID]["player_mmr_change"]  = $this->player_data_mmr_sorted[$replayID]["player_mmr_change"];
      $player_prev_mmr[$data["game_type"]] = (1800 + 40 * $data["player_conservative_rating"]);

      if(array_key_exists($data["hero"], $hero_prev_mmr)){
        $this->player_data_mmr_sorted[$replayID]["hero_mmr_change"] = (1800 + 40 * $data["hero_conservative_rating"]) - $hero_prev_mmr[$data["hero"]];
        $this->player_data[$replayID]["hero_mmr_change"]  = $this->player_data_mmr_sorted[$replayID]["hero_mmr_change"];
        $hero_prev_mmr[$data["hero"]] = (1800 + 40 * $data["hero_conservative_rating"]);

      }else{
        $hero_prev_mmr[$data["hero"]] = 1800;
        $this->player_data_mmr_sorted[$replayID]["hero_mmr_change"] = (1800 + 40 * $data["hero_conservative_rating"]) - $hero_prev_mmr[$data["hero"]];
        $this->player_data[$replayID]["hero_mmr_change"]  = $this->player_data_mmr_sorted[$replayID]["hero_mmr_change"];
      }

      if(array_key_exists($data["role"], $role_prev_mmr)){
        $this->player_data_mmr_sorted[$replayID]["role_mmr_change"] = (1800 + 40 * $data["role_conservative_rating"]) - $role_prev_mmr[$data["role"]];
        $this->player_data[$replayID]["role_mmr_change"]  = $this->player_data_mmr_sorted[$replayID]["role_mmr_change"];
        $role_prev_mmr[$data["role"]] = (1800 + 40 * $data["role_conservative_rating"]);

      }else{
        $role_prev_mmr[$data["role"]] = 1800;
        $this->player_data_mmr_sorted[$replayID]["role_mmr_change"] = (1800 + 40 * $data["role_conservative_rating"]) - $role_prev_mmr[$data["role"]];
        $this->player_data[$replayID]["role_mmr_change"]  = $this->player_data_mmr_sorted[$replayID]["role_mmr_change"];

      }


    }


    $data = json_encode($this->player_data, true);

    if($found == "true"){
      DB::statement("INSERT INTO heroesprofile_cache.player_data " .
      "(region, blizz_id, battletag, data, updated_at) VALUES ($this->region, $this->blizz_id, 'Zemill#1940','" . $data . "', '" . date('Y-m-d H:i:s') . "')" .
      " ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = VALUES(updated_at)");
    }
    $this->getPlayerMMRData();
    return $this->player_data;
  }

  private function grabAllReplays(){
    $roles_by_name = Session::get('roles_by_name');
    $heroes_by_id = Session::get('heroes_by_id');

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

    //$query->where('replay.replayID', '<', 1701807);

    $query->where('region', $this->region);
    $query->where('blizz_id', $this->blizz_id);
    $query->orderBy('game_date', 'ASC');
    //$query->limit('10');

    $this->player_data = $query->get();
    $this->player_data = json_decode(json_encode($this->player_data),true);

    $returnData = array();
    for($i = 0; $i < count($this->player_data); $i++){
      $this->player_data[$i]["role"] = $roles_by_name[$heroes_by_id[$this->player_data[$i]["hero"]]];
      $returnData[$this->player_data[$i]["replayID"]] = $this->player_data[$i];
    }
    $this->player_data = $returnData;
  }

  private function checkForNewReplays(){
    $roles_by_name = Session::get('roles_by_name');
    $heroes_by_id = Session::get('heroes_by_id');

    $latest_replay = 0;
    if(count($this->player_data) > 0){
      $latest_replay = max(array_keys($this->player_data));
    }
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
    $query->where('replay.replayID', '>', $latest_replay);
    $query->where('replay.region', $this->region);
    $query->where('player.blizz_id', $this->blizz_id);
    $query->orderBy('replay.game_date', 'ASC');

    $new_data = $query->get();
    //print_r($query->toSql());
    //print_r($query->getBindings());
    //echo "<br>";
    //echo "<br>";


    $new_data = json_decode(json_encode($new_data),true);

    $returnData = array();
    for($i = 0; $i < count($new_data); $i++){
      $this->player_data[$i]["role"] = $roles_by_name[$heroes_by_id[$this->player_data[$i]["hero"]]];
      $returnData[$new_data[$i]["replayID"]] = $new_data[$i];
    }
    $found = "false";
    if(count($new_data) != 0){
      $this->player_data = $returnData + $this->player_data;
      $found = "true";
    }
    return $found;
  }

  public function getPlayerSummaryStats($game_type, $season){
    $game_types_array = array();
    if($game_type == ""){
      $game_types_array[0] = 1;
      $game_types_array[1] = 2;
      $game_types_array[2] = 3;
      $game_types_array[3] = 4;
      $game_types_array[4] = 5;

    }else{
      $game_types_array[0] = $game_type;
    }

    $start_date = new DateTime("2013-06-14 12:01:00");
    $end_date = new DateTime(date("Y-m-d H:i:s", strtotime('+5 years')));

    if($season != ""){
      $season_dates = Session::get('season_dates');

      $start_date = new DateTime($season_dates[$season]["start_date"]);
      $end_date = new DateTime($season_dates[$season]["end_date"]);

    }

    $data = array();
    $data["wins"] = 0;
    $data["losses"] = 0;
    $data["first_to_ten_win_rate"] = 0;
    $data["second_to_ten_win_rate"] = 0;
    $data["kdr"] = 0;
    $data["kda"] = 0;
    $data["account_level"] = $this->getPlayerAccountLevel();
    $data["win_rate"] = 0;

    $data["Melee Assassin"] = array(
      "wins" => 0,
      "losses" => 0,
      "win_rate" => 0
    );

    $data["Ranged Assassin"] = array(
      "wins" => 0,
      "losses" => 0,
      "win_rate" => 0
    );

    $data["Bruiser"] = array(
      "wins" => 0,
      "losses" => 0,
      "win_rate" => 0
    );

    $data["Support"] = array(
      "wins" => 0,
      "losses" => 0,
      "win_rate" => 0
    );

    $data["Tank"] = array(
      "wins" => 0,
      "losses" => 0,
      "win_rate" => 0
    );

    $data["Healer"] = array(
      "wins" => 0,
      "losses" => 0,
      "win_rate" => 0
    );


    $data["total_time_played"] = 0;

    $deaths = 0;
    $kills = 0;
    $takedowns = 0;

    $first_to_ten_wins = 0;
    $first_to_ten_losses = 0;

    $second_to_ten_wins = 0;
    $second_to_ten_losses = 0;

    foreach ($this->player_data as $replayID => $replay_data){
      $game_date = new DateTime($replay_data['game_date']);

      if($game_date >= $start_date && $game_date <= $end_date){
        if(in_array($replay_data["game_type"], $game_types_array)){
          if($replay_data["winner"] == 1){
            $data["wins"]++;
            $data[$replay_data["role"]]["wins"]++;

            if($replay_data["first_to_ten"] == 1){
              $first_to_ten_wins++;
            }else if($replay_data["first_to_ten"] == 0 && is_numeric($replay_data["first_to_ten"])){
              $second_to_ten_wins++;
            }

          }else{
            $data["losses"]++;
            $data[$replay_data["role"]]["losses"]++;

            if($replay_data["first_to_ten"] == 1){
              $first_to_ten_losses++;
            }else if($replay_data["first_to_ten"] == 0 && is_numeric($replay_data["first_to_ten"])){
              $second_to_ten_losses++;
            }
          }

          $data["total_time_played"] += $replay_data["game_length"];


          $deaths += $replay_data["deaths"];
          $kills += $replay_data["kills"];
          $takedowns += $replay_data["takedowns"];

        }

      }

    }

    if(($data["Melee Assassin"]["wins"] + $data["Melee Assassin"]["losses"]) > 0){
      $data["Melee Assassin"]["win_rate"] = ($data["Melee Assassin"]["wins"] / ($data["Melee Assassin"]["wins"] + $data["Melee Assassin"]["losses"])) * 100;
    }

    if(($data["Ranged Assassin"]["wins"] + $data["Ranged Assassin"]["losses"]) > 0){
      $data["Ranged Assassin"]["win_rate"] = ($data["Ranged Assassin"]["wins"] / ($data["Ranged Assassin"]["wins"] + $data["Ranged Assassin"]["losses"])) * 100;
    }

    if(($data["Bruiser"]["wins"] + $data["Bruiser"]["losses"]) > 0){
      $data["Bruiser"]["win_rate"] = ($data["Bruiser"]["wins"] / ($data["Bruiser"]["wins"] + $data["Bruiser"]["losses"])) * 100;
    }

    if(($data["Support"]["wins"] + $data["Support"]["losses"]) > 0){
      $data["Support"]["win_rate"] = ($data["Support"]["wins"] / ($data["Support"]["wins"] + $data["Support"]["losses"])) * 100;
    }

    if(($data["Tank"]["wins"] + $data["Tank"]["losses"]) > 0){
      $data["Tank"]["win_rate"] = ($data["Tank"]["wins"] / ($data["Tank"]["wins"] + $data["Tank"]["losses"])) * 100;
    }

    if(($data["Healer"]["wins"] + $data["Healer"]["losses"]) > 0){
      $data["Healer"]["win_rate"] = ($data["Healer"]["wins"] / ($data["Healer"]["wins"] + $data["Healer"]["losses"])) * 100;
    }

    if(($first_to_ten_wins + $first_to_ten_losses) > 0){
      $data["first_to_ten_win_rate"] = ($first_to_ten_wins / ($first_to_ten_wins + $first_to_ten_losses)) * 100;
    }


    if(($second_to_ten_wins + $second_to_ten_losses) > 0){
      $data["second_to_ten_win_rate"] = ($second_to_ten_wins / ($second_to_ten_wins + $second_to_ten_losses)) * 100;
    }

    if(($data["wins"] + $data["losses"]) > 0){
      $data["win_rate"] = ($data["wins"] / ($data["wins"] + $data["losses"])) * 100;
    }

    if($deaths > 0){
      $data["kdr"] = $kills / $deaths;
      $data["kda"] = $takedowns / $deaths;
    }else{
      $data["kdr"] = $kills;
      $data["kda"] = $takedowns;
    }
    $data["total_time_played"] = $this->secondsToTime($data["total_time_played"]);

    return collect($data);
  }

  public function getPlayerHeroSummary($game_type, $season){
    $game_types_array = array();
    if($game_type == ""){
      $game_types_array[0] = 1;
      $game_types_array[1] = 2;
      $game_types_array[2] = 3;
      $game_types_array[3] = 4;
      $game_types_array[4] = 5;

    }else{
      $game_types_array[0] = $game_type;
    }

    $start_date = new DateTime("2013-06-14 12:01:00");
    $end_date = new DateTime(date("Y-m-d H:i:s", strtotime('+5 years')));

    if($season != ""){
      $season_dates = Session::get('season_dates');

      $start_date = new DateTime($season_dates[$season]["start_date"]);
      $end_date = new DateTime($season_dates[$season]["end_date"]);

    }

    $hero_data = array();
    foreach ($this->player_data as $replayID => $replay_data){
      $game_date = new DateTime($replay_data['game_date']);

      if($game_date >= $start_date && $game_date <= $end_date){
        if(in_array($replay_data["game_type"], $game_types_array)){

          if(!array_key_exists($replay_data["hero"], $hero_data)){
            $hero_data[$replay_data["hero"]] = array(
              "wins" => 0,
              "losses" => 0,
              "games_played" => 0
            );
          }

          if($replay_data["winner"] == 1){
            $hero_data[$replay_data["hero"]]["wins"]++;
          }else{
            $hero_data[$replay_data["hero"]]["losses"]++;
          }

          $hero_data[$replay_data["hero"]]["games_played"]++;

        }
      }
    }

    foreach ($hero_data as $hero => $data){
      if($data["games_played"] > 0){
        $hero_data[$hero]["win_rate"] = ($data["wins"] / $data["games_played"]) * 100;
      }else{
        $hero_data[$hero]["win_rate"] = 0;
      }
    }

    $games_played_sorted = array_slice(\GlobalFunctions::instance()->sortKeyValueArray($hero_data, "games_played_desc"), 0, 3, true);
    $latest_played_sorted = array_slice($hero_data, 0, 3, true);


    $highest_win_rate_sorted = \GlobalFunctions::instance()->sortKeyValueArray($hero_data, "win_rate_desc");


    $return_highest_win_rate = array();
    foreach ($hero_data as $hero => $data){
      if($highest_win_rate_sorted[$hero]["games_played"] >= 20){
        $return_highest_win_rate[$hero] = $data;
      }
    }

    if(count($return_highest_win_rate) < 3){
      $return_highest_win_rate = array();
      foreach ($hero_data as $hero => $data){
        if($highest_win_rate_sorted[$hero]["games_played"] >= 15){
          $return_highest_win_rate[$hero] = $data;
        }
      }
    }

    if(count($return_highest_win_rate) < 3){
      $return_highest_win_rate = array();
      foreach ($hero_data as $hero => $data){
        if($highest_win_rate_sorted[$hero]["games_played"] >= 10){
          $return_highest_win_rate[$hero] = $data;
        }
      }
    }

    if(count($return_highest_win_rate) < 3){
      $return_highest_win_rate = array();
      foreach ($hero_data as $hero => $data){
        if($highest_win_rate_sorted[$hero]["games_played"] >= 5){
          $return_highest_win_rate[$hero] = $data;
        }
      }
    }

    if(count($return_highest_win_rate) < 3){
      $return_highest_win_rate = array();
      foreach ($hero_data as $hero => $data){
        if($highest_win_rate_sorted[$hero]["games_played"] >= 1){
          $return_highest_win_rate[$hero] = $data;
        }
      }
    }
    $return_highest_win_rate = \GlobalFunctions::instance()->sortKeyValueArray($return_highest_win_rate, "win_rate_desc");
    $return_highest_win_rate = array_slice($return_highest_win_rate, 0, 3, true);

    $return_data = array(
      "games_played" => $games_played_sorted,
      "latest_played" => $latest_played_sorted,
      "win_rate" => $return_highest_win_rate,
    );
    return collect($return_data);
  }

  private function getPlayerAccountLevel(){
    $account_level = 0;
    $battletag_data = Battletag::where([
      ['blizz_id', $this->blizz_id],
      ['region', $this->region]
    ])->get();
    $battletag_data = json_decode(json_encode($battletag_data),true);

    for($i = 0; $i < count($battletag_data); $i++){
      if($battletag_data[$i]["account_level"] > $account_level){
        $account_level = $battletag_data[$i]["account_level"];
      }
    }

    return $account_level;
  }

  private function getPlayerMMRData(){
    $query = DB::table('heroesprofile.master_mmr_data');
    $query->where('type_value', '10000');
    $query->where(function($q) {
            $q->where('game_type', 1)
            ->orWhere('game_type', 2)
            ->orWhere('game_type', 3)
            ->orWhere('game_type', 4)
            ->orWhere('game_type', 5);
        });
    $query->where('blizz_id', $this->blizz_id);
    $query->where('region', $this->region);
    $this->mmr_data = $query->get();
    $this->mmr_data = json_decode(json_encode($this->mmr_data),true);

    $this->getPlayerLeagueTier();
    collect($this->mmr_data);
  }

  private function getPlayerLeagueTier(){
    $leagues = Session::get('leagues_breakdowns');
    //print_r(json_encode($leagues, true));
    if($mmr >= $leagues["master"]["min_mmr"]){
      return "Master";
    }else if($mmr < $leagues["master"]["min_mmr"] && $mmr >= $leagues["diamond"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["diamond"]["min_mmr"] + ($i * $leagues["diamond"]["split"])) && $mmr < ($leagues["diamond"]["min_mmr"]  + (($i + 1) * $leagues["diamond"]["split"]))){
          return "Diamond " . (5 - $i);
        }
      }
      return "Diamond";
    }else if($mmr < $leagues["diamond"]["min_mmr"] && $mmr >= $leagues["platinum"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["platinum"]["min_mmr"] + ($i * $leagues["platinum"]["split"])) && $mmr < ($leagues["platinum"]["min_mmr"]  + (($i + 1) * $leagues["platinum"]["split"]))){
          return "Platinum " . (5 - $i);
        }
      }
      return "Platinum";
    }else if($mmr < $leagues["platinum"]["min_mmr"] && $mmr >= $leagues["gold"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["gold"]["min_mmr"] + ($i * $leagues["gold"]["split"])) && $mmr < ($leagues["gold"]["min_mmr"]  + (($i + 1) * $leagues["gold"]["split"]))){
          return "Gold " . (5 - $i);
        }
      }
      return "Gold";
    }else if($mmr < $leagues["gold"]["min_mmr"] && $mmr >= $leagues["silver"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["silver"]["min_mmr"] + ($i * $leagues["silver"]["split"])) && $mmr < ($leagues["silver"]["min_mmr"]  + (($i + 1) * $leagues["silver"]["split"]))){
          return "Silver " . (5 - $i);
        }
      }
      return "Silver";
    }else{
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["bronze"]["min_mmr"] + ($i * $leagues["bronze"]["split"])) && $mmr < ($leagues["bronze"]["min_mmr"]  + (($i + 1) * $leagues["bronze"]["split"]))){
          return "Bronze " . (5 - $i);
        }
      }
      return "Bronze";
    }
    /*
    for($i = 0; $i < count($this->mmr_data); $i++){
      $query = DB::table('heroesprofile.league_breakdowns');
      $query->where('type_role_hero', '10000');
      $query->where('game_type', $this->mmr_data[$i]["game_type"]);
      $query->where('min_mmr', '<=', (1800 + 40 * $this->mmr_data[$i]["conservative_rating"]));
      $query->orderBy('min_mmr', 'DESC');
      $query->limit(1);
      $data = $query->get();
      //print_r($query->toSql());
      //print_r($query->getBindings());

      $data = json_decode(json_encode($data),true);
      $this->mmr_data[$i]["league_tier"] = getPlayerLeagueTierSplit($this->mmr_data[$i]["game_type"] , (1800 + 40 * $this->mmr_data[$i]["conservative_rating"]), $data[0]['league_tier']);
    }
    */
  }

  private function getPlayerLeagueTierSplit($game_type, $player_mmr, $league_tier){
  }

  public function getMMRVar(){
    return $this->mmr_data;
  }
  private function secondsToTime($inputSeconds) {
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;

    // Extract days
    $days = floor($inputSeconds / $secondsInADay);

    // Extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // Extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);


    // Extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // Format and return
    $timeParts = [];
    $sections = [
        'day' => (int)$days,
        'hour' => (int)$hours,
        'minute' => (int)$minutes,
        'second' => (int)$seconds,
    ];

    foreach ($sections as $name => $value){
        if ($value > 0){
            $timeParts[] = $value. ' '.$name.($value == 1 ? '' : 's');
        }
    }

    return implode(', ', $timeParts);
  }

  public function getLatestPlayed($count_return){
     return array_slice($player_data, 0, $count_return);
  }
}
