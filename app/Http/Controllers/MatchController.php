<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MatchController extends Controller
{
  public function show(){
    return view('match');
  }

  public function getMatchData(Request $request){
    /*
    $replay_data = \App\Models\Replay::select(
      "replay.replayID",
      "replay.game_type",
      "replay.game_date",
      "replay.game_length",
      "replay.game_map",
      "replay.region",
      "player.hero",
      "player.team",
      "player.winner",
      "player.player_conservative_rating",
      "player.player_change",
      "player.hero_conservative_rating",
      "player.hero_change",
      "player.role_conservative_rating",
      "player.role_change",
      "player.mmr_date_parsed",
      "scores.kills",
      "scores.takedowns",
      "scores.deaths",
      "scores.first_to_ten",
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
    ->where('replay.replayID', '=', $request["replayID"])
    ->get();

    return $replay_data;
    */

    return $this->getReplayBans($request["replayID"]);
  }
  private function getReplayBans($replayID){
  }

  public function getReplayExperienceBreakdown(Request $request){
  }
}
