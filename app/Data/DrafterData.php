<?php
namespace App\Data;

class DrafterData
{
  private $game_versions_minor;
  private $game_type;
  private $region;
  private $game_map;
  private $hero_level;
  private $player_league_tier;
  private $hero_league_tier;
  private $role_league_tier;

  public function __construct($game_versions_minor, $game_type, $region, $game_map, $hero_level, $player_league_tier, $hero_league_tier, $role_league_tier) {
    $this->game_versions_minor = $game_versions_minor;
    $this->game_type = $game_type;
    $this->region = $region;
    $this->game_map = $game_map;
    $this->hero_level = $hero_level;
    $this->player_league_tier = $player_league_tier;
    $this->hero_league_tier = $hero_league_tier;
    $this->role_league_tier = $role_league_tier;
  }
}
