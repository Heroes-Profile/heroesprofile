<?php
namespace App\Data\Profile;

//use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Hero
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

  public function getHeroAllData(){
    $game_type = $this->game_type;
    $season = $this->season;
    $hero_data = \App\Models\Replay::select(
      "player.hero",
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
    ->groupBy(['hero', 'winner'])
    ->get();

    $return_data = array();
    $heroes = \App\Models\Hero::select("id")->get();
    for($i = 0; $i < count($heroes); $i++){
      $return_data[$heroes[$i]["id"]]["wins"] = 0;
      $return_data[$heroes[$i]["id"]]["losses"] = 0;
    }
    for($i = 0; $i < count($hero_data); $i++){
      if($hero_data[$i]["winner"] == 1){
        $return_data[$hero_data[$i]["hero"]]["wins"] = $hero_data[$i]["total"];
      }else{
        $return_data[$hero_data[$i]["hero"]]["losses"] = $hero_data[$i]["total"];
      }
    }
    return $return_data;
  }
}
