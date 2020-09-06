<?php
namespace App\Data\Profile;

//use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Matchups
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

  public function getMatchupData(){
    $returnData = array();
    $heroes = \App\Models\Hero::select("id")->get();
    for($i = 0; $i < count($heroes); $i++){
      $returnData[$heroes[$i]["id"]]["ally"]["wins"] = 0;
      $returnData[$heroes[$i]["id"]]["ally"]["losses"] = 0;
      $returnData[$heroes[$i]["id"]]["enemy"]["wins"] = 0;
      $returnData[$heroes[$i]["id"]]["enemy"]["losses"] = 0;

    }

    $returnData = $this->getAllyEnemyData(0, $returnData);
    $returnData = $this->getAllyEnemyData(1, $returnData);

    return $returnData;
  }

  private function getAllyEnemyData($team, $returnData){
    $game_type = $this->game_type;
    $season = $this->season;

    $replay_data = \App\Models\Replay::select('replay.replayID')
      ->join('player', 'player.replayID', '=', 'replay.replayID')
      ->where('replay.region', $this->region)
      ->where('player.blizz_id', $this->blizz_id)
      ->when($game_type != "", function($query) use ($game_type) {
          return $query->where('replay.game_type', $game_type);
        })
      ->when($season != "", function($query) use ($season) {
          return $query->where('replay.game_type', $season);
        })
      ->where('team', $team)
      ->get();

    $replayIDs = array();
    for($i = 0; $i< count($replay_data); $i++){
      $replayIDs[$i] = $replay_data[$i]["replayID"];
    }

    $matchup_data = \App\Models\Replay::select('hero', 'team', 'winner')->selectRaw('COUNT(*) as total')
      ->join('player', 'player.replayID', '=', 'replay.replayID')
      ->where('replay.region', $this->region)
      ->where('player.blizz_id', '!=', $this->blizz_id)
      ->when($game_type != "", function($query) use ($game_type) {
          return $query->where('replay.game_type', $game_type);
        })
      ->when($season != "", function($query) use ($season) {
          return $query->where('replay.game_type', $season);
        })
      ->whereIn('replay.replayID', $replayIDs)
      ->groupBy(['hero', 'team', 'winner'])
      ->get();


    for($i = 0; $i< count($matchup_data); $i++){
      if($matchup_data[$i]["team"] == $team){
        if($matchup_data[$i]["winner"] == 0){
          $returnData[$matchup_data[$i]["hero"]]["ally"]["losses"] += $matchup_data[$i]["total"];
        }else{
          $returnData[$matchup_data[$i]["hero"]]["ally"]["wins"] += $matchup_data[$i]["total"];
        }
      }else{
        if($matchup_data[$i]["winner"] == 0){
          $returnData[$matchup_data[$i]["hero"]]["enemy"]["losses"] += $matchup_data[$i]["total"];
        }else{
          $returnData[$matchup_data[$i]["hero"]]["enemy"]["wins"] += $matchup_data[$i]["total"];
        }
      }
    }

    return $returnData;

  }

}
