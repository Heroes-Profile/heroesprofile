<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;
use Session;

class ProfileData
{
  public static function instance()
  {
      return new ProfileData();
  }

  private $player_data;
  private $player_data_mmr_sorted;

  public function getPlayerData(){
    $query = DB::table('heroesprofile_cache.player_data');
    $query->where('region', 1);
    $query->where('blizz_id', 67280);
    $cache_data = $query->get();
    $cache_data = json_decode(json_encode($cache_data),true);

    if(count($cache_data) > 0){
      if($cache_data[0]["data"] == "null"){
        $this->grabAllReplays();
        //echo "Grabbing all replay data";
        //echo "<br>";
        //echo "<br>";

      }else{
        $this->player_data = json_decode($cache_data[0]["data"], true);
        $this->checkForNewReplays();
        //echo "Checking for new replay data using cached data";
        //echo "<br>";
        //echo "<br>";

      }
    }else{
      $this->grabAllReplays();
      //echo "Grabbing all replay data";
      //echo "<br>";
      //echo "<br>";
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


    DB::statement("INSERT INTO heroesprofile_cache.player_data " .
      "(region, blizz_id, battletag, data, updated_at) VALUES ('1', '67280', 'Zemill#1940','" . $data . "', '" . date('Y-m-d H:i:s') . "')" .
      " ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = VALUES(updated_at)");
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

    $query->where('region', 1);
    $query->where('blizz_id', 67280);
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
    $query->where('replay.region', 1);
    $query->where('player.blizz_id', 67280);
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

    if(count($new_data) != 0){
      $this->player_data = $returnData + $this->player_data;
    }

  }

  public function getLatestPlayed($count_return){
     //$sorted_array = \GlobalFunctions::instance()->sortKeyValueArray($this->player_data, "game_date_desc");
     return array_slice($sorted_array, 0, $count_return);
  }
}
