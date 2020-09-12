<?php
namespace App\Data\Match;

class MatchTeam
{
  private $team_count;
  public $players = array();
  public $bans = array();
  public $avg_account_level;
  public $team_level;
  public $takedowns;
  public $avg_player_mmr;
  public $avg_hero_mmr;
  public $avg_role_mmr;

  public function __construct() {
    $this->team_count = 0;
    $this->avg_account_level = 0;
    $this->team_level = 0;
    $this->takedowns = 0;
    $this->avg_player_mmr = 0;
    $this->avg_hero_mmr = 0;
    $this->avg_role_mmr = 0;
  }

  public function addPlayer($player_data){
    $player = new MatchPlayer($player_data["blizz_id"], $player_data["region"]);
    $player->setBattletag($player_data["battletag"]);
    $player->getPatreon();
    $player->getOptOut();
    $this->avg_account_level += $player->setAccountLevel();
    if($this->takedowns < $player_data["takedowns"]){
      $this->takedowns = $player_data["takedowns"];
    }
    $player->setHero($player_data["hero"]);
    $player->setHeroLevel($player_data["hero_level"], $player_data["mastery_taunt"]);
    $player->setPlayerMMR(1800 + 40 * $player_data["player_conservative_rating"]);
    $this->avg_player_mmr += $player->player_mmr;
    $player->setHeroMMR(1800 + 40 * $player_data["hero_conservative_rating"]);
    $this->avg_hero_mmr += $player->hero_mmr;
    $player->setRoleMMR(1800 + 40 * $player_data["role_conservative_rating"]);
    $this->avg_role_mmr += $player->role_mmr;


    $player->setScores($player_data);

    $this->players[$this->team_count] = $player;
    $this->incrementTeamPlayerCount();
  }
  public function addBans($replayID, $team){
    $bans = \App\Models\ReplayBan::select('hero')->where('replayID', $replayID)->where('team', $team)->orderBy('ban_id', 'ASC')->get();
    $ban_counter = 0;
    for($i = 0; $i < count($bans); $i++){
      if($bans[$i]->hero != 0){
        $this->bans[$ban_counter] = \App\Models\Hero::select('id', 'name', 'short_name')->where('id', $bans[$i]->hero)->first();
        $ban_counter++;
      }
    }
  }

  public function setTeamLevel($level){
    $this->team_level = $level;
  }
  private function incrementTeamPlayerCount(){
    $this->team_count++;
  }
}
