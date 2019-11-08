<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;

class PlayerData
{
  public static function instance()
  {
      return new PlayerData();
  }

  public $player_data;

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

    $data = json_encode($this->player_data, true);
    DB::statement("INSERT INTO heroesprofile_cache.player_data " .
      "(region, blizz_id, battletag, data) VALUES ('1', '67280', 'Zemill#1940','" . $data . "')" .
      " ON DUPLICATE KEY UPDATE data = VALUES(data)");
    return $this->player_data;
  }
  private function grabAllReplays(){
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
      $returnData[$this->player_data[$i]["replayID"]] = $this->player_data[$i];
    }
    $this->player_data = $returnData;
  }

  private function checkForNewReplays(){

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
      $returnData[$new_data[$i]["replayID"]] = $new_data[$i];
    }

    if(count($new_data) != 0){
      $this->player_data = $returnData + $this->player_data;
    }

  }
}
