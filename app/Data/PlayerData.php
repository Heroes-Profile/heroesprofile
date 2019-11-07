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


    //$query->where('replay.replayID', '<', 20511323);

    $query->where('region', 1);
    $query->where('blizz_id', 67280);
    $query->orderBy('game_date', 'DESC');

    $this->player_data = $query->get();
    $this->player_data = json_decode(json_encode($this->player_data),true);

    $this->checkForNewReplays();
  }

  public function checkForNewReplays(){
    $latest_replay = 0;
    if(count($this->player_data) > 0){
      $latest_replay = $this->player_data[0]["replayID"];
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
    $query->orderBy('replay.game_date', 'DESC');

    $new_data = $query->get();
    $new_data = json_decode(json_encode($new_data),true);


    if(count($new_data) != 0){
      $this->player_data = $new_data + $this->player_data;
    }

    print_r(json_encode($this->player_data, true));
  }
}
