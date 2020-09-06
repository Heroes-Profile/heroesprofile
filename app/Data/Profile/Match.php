<?php
namespace App\Data\Profile;

//use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Match
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

  public function getMatchData(){
    $game_type = $this->game_type;
    $season = $this->season;
    $replay_data = \App\Models\Replay::select(
      "replay.replayID",
      "replay.game_type",
      "replay.game_date",
      "replay.game_map",
      "player.hero",
      "player.winner",
      "player.player_conservative_rating",
      "talents.level_one",
      "talents.level_four",
      "talents.level_seven",
      "talents.level_ten",
      "talents.level_thirteen",
      "talents.level_sixteen",
      "talents.level_twenty"
      )
    ->join('player', 'player.replayID', '=', 'replay.replayID')
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
    ->where('replay.region', $this->region)
    ->where('player.blizz_id', $this->blizz_id)
    ->when($game_type != "", function($query) use ($game_type) {
        return $query->where('replay.game_type', $game_type);
      })
    ->when($season != "", function($query) use ($season) {
        return $query->where('replay.game_type', $season);
      })
    ->orderBy('game_date', 'DESC')
    ->limit(100)
    ->get();
    //Add in pagination
    return $replay_data;
  }
}
