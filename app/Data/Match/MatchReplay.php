<?php
namespace App\Data\Match;

class MatchReplay
{
  private $replayID;
  private $teams = array();

  public function __construct($replayID) {
    $this->replayID = $replayID;
    $match_data = $this->getMatchData();
  }


  private function getMatchData(){
    $replay_data = \App\Models\Replay::select(
      "replay.replayID",
      "replay.game_type",
      "replay.game_date",
      "replay.game_length",
      "replay.game_map",
      "replay.region",
      "player.blizz_id",
      "player.battletag",
      "player.hero",
      "player.hero_level",
      "player.mastery_taunt",
      "player.team",
      "player.winner",
      "player.player_conservative_rating",
      "player.player_change",
      "player.hero_conservative_rating",
      "player.hero_change",
      "player.role_conservative_rating",
      "player.role_change",
      "player.mmr_date_parsed",
      "scores.level",
      "scores.kills",
      "scores.assists",
      "scores.takedowns",
      "scores.deaths",
      "scores.highest_kill_streak",
      "scores.hero_damage",
      "scores.siege_damage",
      "scores.structure_damage",
      "scores.minion_damage",
      "scores.creep_damage",
      "scores.summon_damage",
      "scores.time_cc_enemy_heroes",
      "scores.healing",
      "scores.self_healing",
      "scores.damage_taken",
      "scores.experience_contribution",
      "scores.town_kills",
      "scores.time_spent_dead",
      "scores.merc_camp_captures",
      "scores.watch_tower_captures",
      //"scores.meta_experience", Useless attr
      "scores.match_award",
      "scores.protection_allies",
      "scores.silencing_enemies",
      "scores.rooting_enemies",
      "scores.stunning_enemies",
      "scores.clutch_heals",
      "scores.escapes",
      "scores.vengeance",
      "scores.outnumbered_deaths",
      "scores.teamfight_escapes",
      "scores.teamfight_healing",
      "scores.teamfight_damage_taken",
      "scores.teamfight_hero_damage",
      "scores.multikill",
      "scores.physical_damage",
      "scores.spell_damage",
      "scores.regen_globes",
      "scores.first_to_ten",
      "scores.time_on_fire",
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
      ->where('replay.replayID', '=', $this->replayID)
      ->get();
      $team_0 = new MatchTeam;
      $team_1 = new MatchTeam;
      for($i = 0; $i < count($replay_data); $i++){
        if($replay_data[$i]["team"] == 0){
          $team_0->addPlayer($replay_data[$i]);
          $team_0->addBans($this->replayID, $i);
          $team_0->setTeamLevel($replay_data[$i]->level);
        }else{
          $team_1->addPlayer($replay_data[$i]);
          $team_1->addBans($this->replayID, $i);
          $team_1->setTeamLevel($replay_data[$i]->level);
        }
      }
      $team_0->avg_account_level /= 5;
      $team_0->avg_player_mmr /= 5;
      $team_0->avg_hero_mmr /= 5;
      $team_0->avg_role_mmr /= 5;

      $team_1->avg_account_level /= 5;
      $team_1->avg_player_mmr /= 5;
      $team_1->avg_hero_mmr /= 5;
      $team_1->avg_role_mmr /= 5;

      $this->teams[0] = $team_0;
      $this->teams[1] = $team_1;
    }
    /*
    private function getReplayBans($replayID){
      $replay_bans = \App\Models\ReplayBan::where('replayID', $replayID)->get();
      return $replay_bans;
    }

    public function getReplayExperienceBreakdown($replayID){
      $replay_bans = \App\Models\ReplayExperienceBreakdown::where('replayID', $replayID)->get();
      return $replay_bans;
    }

    */
    public function getTeams(){
      return $this->teams;
    }
  }
