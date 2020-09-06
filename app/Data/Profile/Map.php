<?php
namespace App\Data\Profile;

//use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Map
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

  public function getMapAllData(){
    $game_type = $this->game_type;
    $season = $this->season;
    $map_data = \App\Models\Replay::select(
      "replay.game_map",
      "player.winner",
      )
    ->selectRaw('COUNT(*) as total')
    ->join('player', 'player.replayID', '=', 'replay.replayID')
    /*
    ->join('scores', function($join)
      {
        $join->on('scores.replayID', '=', 'replay.replayID');
        $join->on('scores.battletag', '=', 'player.battletag');
      }
    )
    ->join('talents', function($join)
      {
        $join->on('talents.replayID', '=', 'replay.replayID');
        $join->on('talents.battletag', '=', 'player.battletag');
      }
    )
    */
    ->where('replay.region', $this->region)
    ->where('player.blizz_id', $this->blizz_id)
    ->when($game_type != "", function($query) use ($game_type) {
        return $query->where('replay.game_type', $game_type);
      })
    ->when($season != "", function($query) use ($season) {
        return $query->where('replay.game_type', $season);
      })
    ->groupBy(['game_map', 'winner'])
    ->get();

    $return_data = array();
    $maps = \App\Models\Map::select("map_id")->where('type', 'standard')->get();
    for($i = 0; $i < count($maps); $i++){
      $return_data[$maps[$i]["map_id"]]["wins"] = 0;
      $return_data[$maps[$i]["map_id"]]["losses"] = 0;
    }
    for($i = 0; $i < count($map_data); $i++){
      if($map_data[$i]["winner"] == 1){
        $return_data[$map_data[$i]["game_map"]]["wins"] = $map_data[$i]["total"];
      }else{
        $return_data[$map_data[$i]["game_map"]]["losses"] = $map_data[$i]["total"];
      }
    }
    return $return_data;
  }
}
