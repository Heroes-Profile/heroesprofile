<?php
namespace App\Data\Profile;

//use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FriendsAndFoes
{
  private $blizz_id;
  private $region;
  private $game_type;
  private $season;

  public function __construct($blizz_id, $region, $game_type, $season) {
    $this->blizz_id = $blizz_id;
    $this->region = $region;
    $this->game_type = $game_type;
    $this->season = $season;
  }

  public function getFriendsAndFoesData(){
    $allies = array();
    $blizz_ids_array = array();
    $blizz_ids_counter = 0;

    for($i = 0; $i < 2; $i++){
      $replayID_data = \App\Models\Replay::select('replay.replayID')
        ->join('player', 'player.replayID', '=', 'replay.replayID')
        ->where('region', $this->region)
        ->where('blizz_id', $this->blizz_id)
        ->where('team', $i)
        ->get();

      $replayIDs = array();
      for($j = 0; $j < count($replayID_data); $j++){
        $replayIDs[$j] = $replayID_data[$j]["replayID"];
      }

      $player_data = \App\Models\Replay::selectRaw('hero, team, winner, blizz_id, COUNT(*) AS total')
        ->join('player', 'player.replayID', '=', 'replay.replayID')
        ->whereIn('replay.replayID', $replayIDs)
        ->where('team', $i)
        ->groupBy(['hero' , 'team' , 'winner' , 'blizz_id'])
        ->get();

      $battletags = array();
      $battletags_latest = array();
      $finalData = array();
      $prevReplay = "";
      $data = array();

      for($j = 0; $j < count($player_data); $j++){
        if($player_data[$j]["blizz_id"] != $this->blizz_id){
          if(!array_key_exists($player_data[$j]["blizz_id"], $allies)){

            $allies[$player_data[$j]["blizz_id"]]["games_played"] = 0;
            $allies[$player_data[$j]["blizz_id"]]["wins"] = 0;
            $allies[$player_data[$j]["blizz_id"]]["losses"] = 0;
            $allies[$player_data[$j]["blizz_id"]]["heroes"] = array();
          }

          if(!array_key_exists($player_data[$j]["hero"], $allies[$player_data[$j]["blizz_id"]]["heroes"])){
            $allies[$player_data[$j]["blizz_id"]]["heroes"][$player_data[$j]["hero"]]["games_played"] = 0;
          }

          $allies[$player_data[$j]["blizz_id"]]["games_played"] += $player_data[$j]["total"];
          if($player_data[$j]["winner"] == 1){
            $allies[$player_data[$j]["blizz_id"]]["wins"] += $player_data[$j]["total"];
          }else{
            $allies[$player_data[$j]["blizz_id"]]["losses"] += $player_data[$j]["total"];
          }
          $allies[$player_data[$j]["blizz_id"]]["heroes"][$player_data[$j]["hero"]]["games_played"] += $player_data[$j]["total"];
        }
      }
    }

    uasort($allies, [$this, 'cmp']);
    $allies = array_slice($allies, 0, 50, true);
    $allies_blizz_ids = array_keys($allies);
    for($i = 0; $i < count($allies_blizz_ids); $i++){
      if(!array_key_exists($allies_blizz_ids[$i], $blizz_ids_array)){
        $blizz_ids_array[$blizz_ids_counter] = $allies_blizz_ids[$i];
        $blizz_ids_counter++;
      }
    }

    $enemies = array();
    for($i = 0; $i < 2; $i++){
      $second_value = $i;
      if($i == 0){
        $second_value = $i + 1;
      }else{
        $second_value = $i - 1;
      }

      $replayID_data = \App\Models\Replay::select('replay.replayID')
        ->join('player', 'player.replayID', '=', 'replay.replayID')
        ->where('region', $this->region)
        ->where('blizz_id', $this->blizz_id)
        ->where('team', $i)
        ->get();

      $replayIDs = array();
      for($j = 0; $j < count($replayID_data); $j++){
        $replayIDs[$j] = $replayID_data[$j]["replayID"];
      }

      $player_data = \App\Models\Replay::selectRaw('hero, team, winner, blizz_id, COUNT(*) AS total')
        ->join('player', 'player.replayID', '=', 'replay.replayID')
        ->whereIn('replay.replayID', $replayIDs)
        ->where('team', $second_value)
        ->groupBy(['hero' , 'team' , 'winner' , 'blizz_id'])
        ->get();

      $battletags = array();
      $battletags_latest = array();
      $finalData = array();
      $prevReplay = "";
      $data = array();

      for($j = 0; $j < count($player_data); $j++){
        if($player_data[$i]["blizz_id"] != $this->blizz_id){
          if(!array_key_exists($player_data[$i]["blizz_id"], $enemies)){
            $enemies[$player_data[$i]["blizz_id"]]["games_played"] = 0;
            $enemies[$player_data[$i]["blizz_id"]]["wins"] = 0;
            $enemies[$player_data[$i]["blizz_id"]]["losses"] = 0;
            $enemies[$player_data[$i]["blizz_id"]]["heroes"] = array();
          }

          if(!array_key_exists($player_data[$i]["hero"], $enemies[$player_data[$i]["blizz_id"]]["heroes"])){
            $enemies[$player_data[$i]["blizz_id"]]["heroes"][$player_data[$i]["hero"]]["games_played"] = 0;
          }

          $enemies[$player_data[$i]["blizz_id"]]["games_played"] += $player_data[$i]["total"];
          if($player_data[$i]["winner"] == 1){
            $enemies[$player_data[$i]["blizz_id"]]["wins"] += $player_data[$i]["total"];
          }else{
            $enemies[$player_data[$i]["blizz_id"]]["losses"] += $player_data[$i]["total"];
          }
          $enemies[$player_data[$i]["blizz_id"]]["heroes"][$player_data[$i]["hero"]]["games_played"] += $player_data[$i]["total"];
        }

      }
    }
    uasort($enemies, [$this, 'cmp']);
    $enemies = array_slice($enemies, 0, 50, true);
    $enemies_blizz_ids = array_keys($enemies);


    for($i = 0; $i < count($enemies_blizz_ids); $i++){
      if(!array_key_exists($enemies_blizz_ids[$i], $blizz_ids_array)){
        $blizz_ids_array[$blizz_ids_counter] = $enemies_blizz_ids[$i];
        $blizz_ids_counter++;
      }
    }

    $battletagData = \App\Models\Battletag::select('blizz_id', 'battletag', 'latest_game')
      ->whereIn('blizz_id', $blizz_ids_array)
      ->where('region', $this->region)
      ->get();

    $battletags = array();

    for($i = 0; $i < count($battletagData); $i++){
      if(array_key_exists($battletagData[$i]["blizz_id"], $battletags)){
        if($battletagData[$i]["latest_game"] > $battletags[$battletagData[$i]["blizz_id"]]["latest_game"] ){
          $battletags[$battletagData[$i]["blizz_id"]]["battletag"] = $battletagData[$i]["battletag"];
          $battletags[$battletagData[$i]["blizz_id"]]["latest_game"] = $battletagData[$i]["latest_game"];
        }
      }else{
        $battletags[$battletagData[$i]["blizz_id"]]["battletag"] = $battletagData[$i]["battletag"];
        $battletags[$battletagData[$i]["blizz_id"]]["latest_game"] = $battletagData[$i]["latest_game"];
      }
    }

    foreach($allies as $blizz_id => $data){
      $allies[$blizz_id]["battletag"] = $battletags[$blizz_id]["battletag"];

      $heroes = $allies[$blizz_id]["heroes"];
      uasort($heroes, [$this, 'cmp']);
      foreach($heroes as $hero => $data){
        $allies[$blizz_id]["most_played_hero"] = $hero;
        break;
      }
    }

    foreach($enemies as $blizz_id => $data){
      $enemies[$blizz_id]["battletag"] = $battletags[$blizz_id]["battletag"];

      $heroes = $enemies[$blizz_id]["heroes"];
      uasort($heroes, [$this, 'cmp']);
      foreach($heroes as $hero => $data){
        $enemies[$blizz_id]["most_played_hero"] = $hero;
        break;
      }
    }

    //print_r(json_encode($allies, true));
    return array($allies, $enemies);
    //return array($return_allies, $return_enemies);
  }

  private function cmp( $a, $b ) {
    if($a["games_played"] ==  $b["games_played"] ){ return 0 ; }
    return ($a["games_played"] > $b["games_played"]) ? -1 : 1;
  }
}
