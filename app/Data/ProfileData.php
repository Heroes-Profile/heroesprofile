<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;
use Session;
use App\Battletag;

class ProfileData
{
  private $blizz_id;
  private $region;

  private $player_data;
  private $player_data_mmr_sorted;

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
      }else{
        $this->player_data = json_decode($cache_data[0]["data"], true);
        $found = $this->checkForNewReplays();
      }
    }else{
      $this->grabAllReplays();
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

    if($found){
      DB::statement("INSERT INTO heroesprofile_cache.player_data " .
      "(region, blizz_id, battletag, data, updated_at) VALUES ($this->region, $this->blizz_id, 'Zemill#1940','" . $data . "', '" . date('Y-m-d H:i:s') . "')" .
      " ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = VALUES(updated_at)");
    }
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
    $found = false;
    if(count($new_data) != 0){
      $this->player_data = $returnData + $this->player_data;
      $found = true;
    }
    return $found;
  }

  public function getPlayerSummaryStats($game_type, $season){
    $data = array();
    $data["wins"] = 0;
    $data["losses"] = 0;
    $data["first_to_ten_win_rate"] = 0;
    $data["second_to_ten_win_rate"] = 0;
    $data["kdr"] = 0;
    $data["kda"] = 0;
    $data["account_level"] = $this->getPlayerAccountLevel();
    $data["win_rate"] = 0;
    $data["melee_assassin_win_rate"] = 0;
    $data["ranged_assassin_win_rate"] = 0;
    $data["bruiser_win_rate"] = 0;
    $data["support_win_rate"] = 0;
    $data["tank_win_rate"] = 0;
    $data["healer_win_rate"] = 0;
    $data["total_time_played"] = 0;

    $deaths = 0;
    $kills = 0;
    $takedowns = 0;

    $first_to_ten_wins = 0;
    $first_to_ten_losses = 0;

    $second_to_ten_wins = 0;
    $second_to_ten_losses = 0;

    foreach ($this->player_data as $replayID => $replay_data){
      if($replay_data["winner"] == 1){
        $data["wins"]++;
      }else{
        $data["losses"]++;
      }

      $data["total_time_played"] += $replay_data["game_length"];


      $deaths += $replay_data["deaths"];
      $kills += $replay_data["kills"];
      $takedowns += $replay_data["takedowns"];

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

    return collect($data);
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

  public function getLatestPlayed($count_return){
     return array_slice($player_data, 0, $count_return);
  }
}
