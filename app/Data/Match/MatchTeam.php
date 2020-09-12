<?php
namespace App\Data\Match;

class MatchTeam
{
  private $team_count;
  public $players = array();

  public function __construct() {
    $this->team_count = 0;
  }

  public function addPlayer($player_data){
    $player = new MatchPlayer($player_data["blizz_id"], $player_data["region"]);
    $player->setBattletag($player_data["battletag"]);
    $player->getPatreon();
    $player->getOptOut();
    $player->setAccountLevel();
    $player->setHero($player_data["hero"]);
    $player->setHeroLevel($player_data["hero_level"], $player_data["mastery_taunt"]);
    $player->setPlayerMMR(1800 + 40 * $player_data["player_conservative_rating"]);
    $player->setHeroMMR(1800 + 40 * $player_data["hero_conservative_rating"]);
    $player->setRoleMMR(1800 + 40 * $player_data["role_conservative_rating"]);

    $this->players[$this->team_count] = $player;
    $this->incrementTeamPlayerCount();
  }

  private function incrementTeamPlayerCount(){
    $this->team_count++;
  }
}
