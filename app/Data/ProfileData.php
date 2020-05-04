<?php
namespace App\Data;

use Illuminate\Support\Facades\DB;

class ProfileData
{
  private $blizz_id;
  private $region;

  public function __construct($blizz_id, $region) {
    $this->blizz_id = $blizz_id;
    $this->region = $region;
  }

  public function getPlayerProfileData(){
    $player_profile_data = \App\Models\GlobalHeroStats::select(
      'replayID',
      'game_type',
      'game_date',
      'game_length',
      'game_map',
      'game_version',
      'region',
      'date_added',
      'mmr_ran',
      'globals_ran',
      'player_match_quality',
      'hero_match_quality', '
      role_match_quality'
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
  }
}
