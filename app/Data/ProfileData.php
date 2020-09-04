<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;

class ProfileData
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

  public function getPlayerProfileData(){
    $profile_cache = \App\Models\ProfilePageCache::Filters($blizz_id, $region, $game_type, $season)->get();
    /*
    $player_profile_data = \App\Models\Replay::select(
      'replay.replayID'
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
      ->orderBy('replay.game_date', 'ASC')
      ->get();
    return $player_profile_data;
    */
  }
}
