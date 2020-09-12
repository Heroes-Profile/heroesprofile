<?php
namespace App\Data\Match;

//Feels pointless to individually delcare each value here.  But incase I need to alter a value, it will be easier to do so in the future.
class MatchScores
{
  private $player_data;

  public $kills;
  public $assists;
  public $takedowns;
  public $deaths;
  public $highest_kill_streak;
  public $hero_damage;
  public $siege_damage;
  public $structure_damage;
  public $minion_damage;
  public $creep_damage;
  public $summon_damage;
  public $time_cc_enemy_heroes;
  public $healing;
  public $self_healing;
  public $damage_taken;
  public $experience_contribution;
  public $town_kills;
  public $time_spent_dead;
  public $merc_camp_captures;
  public $watch_tower_captures;
  //public $meta_experience;  Useless attr
  public $match_award;
  public $protection_allies;
  public $silencing_enemies;
  public $rooting_enemies;
  public $stunning_enemies;
  public $clutch_heals;
  public $escapes;
  public $vengeance;
  public $outnumbered_deaths;
  public $teamfight_escapes;
  public $teamfight_healing;
  public $teamfight_damage_taken;
  public $teamfight_hero_damage;
  public $multikill;
  public $physical_damage;
  public $spell_damage;
  public $regen_globes;
  public $first_to_ten;
  public $time_on_fire;

  public function __construct($player_data) {
    $this->player_data = $player_data;

    $this->setKills();
    $this->setAssists();
    $this->setTakedowns();
    $this->setDeaths();
    $this->setHighestKillStreak();
    $this->setHeroDamage();
    $this->setSiegeDamage();
    $this->setStructureDamage();
    $this->setMinionDamage();
    $this->setCreepDamage();
    $this->setSummonDamage();
    $this->setTimeCCEnemyHeroes();
    $this->setHealing();
    $this->setSelfHealing();
    $this->setDamageTaken();
    $this->setExperienceContribution();
    $this->setTownKills();
    $this->setTimeSpentDead();
    $this->setMercCampCaptures();
    $this->setMatchAward();
    $this->setProtectionAllies();
    $this->setSilencingEnemies();
    $this->setRootingEnemies();
    $this->setStunningEnemies();
    $this->setClutchHeals();
    $this->setEscapes();
    $this->setVengeance();
    $this->setOutnumberedDeaths();
    $this->setTeamfightEscapes();
    $this->setTeamfightHealing();
    $this->setTeamfightDamageTaken();
    $this->setTeamfightHeroDamage();
    $this->setMultikill();
    $this->setPhysicalDamage();
    $this->setSpellDamage();
    $this->setRegenGlobes();
    $this->setFirstToTen();
    $this->setTimeOnFire();
  }

  private function setKills(){
    $this->kills = $this->player_data->kills;
  }

  private function setAssists(){
    $this->assists = $this->player_data->assists;
  }

  private function setTakedowns(){
    $this->takedowns = $this->player_data->takedowns;
  }

  private function setDeaths(){
    $this->deaths = $this->player_data->deaths;
  }

  private function setHighestKillStreak(){
    $this->highest_kill_streak = $this->player_data->highest_kill_streak;
  }

  private function setHeroDamage(){
    $this->hero_damage = $this->player_data->hero_damage;
  }

  private function setSiegeDamage(){
    $this->siege_damage = $this->player_data->siege_damage;
  }

  private function setStructureDamage(){
    $this->structure_damage = $this->player_data->structure_damage;
  }

  private function setMinionDamage(){
    $this->minion_damage = $this->player_data->minion_damage;
  }

  private function setCreepDamage(){
    $this->creep_damage = $this->player_data->creep_damage;
  }

  private function setSummonDamage(){
    $this->summon_damage = $this->player_data->summon_damage;
  }

  private function setTimeCCEnemyHeroes(){
    $this->time_cc_enemy_heroes = $this->player_data->time_cc_enemy_heroes;
  }

  private function setHealing(){
    $this->healing = $this->player_data->healing;
  }

  private function setSelfHealing(){
    $this->self_healing = $this->player_data->self_healing;
  }

  private function setDamageTaken(){
    $this->damage_taken = $this->player_data->damage_taken;
  }

  private function setExperienceContribution(){
    $this->experience_contribution = $this->player_data->experience_contribution;
  }

  private function setTownKills(){
    $this->town_kills = $this->player_data->town_kills;
  }

  private function setTimeSpentDead(){
    $this->time_spent_dead = $this->player_data->time_spent_dead;
  }

  private function setMercCampCaptures(){
    $this->merc_camp_captures = $this->player_data->merc_camp_captures;
  }

  private function setWatchTowerCaptures(){
    $this->watch_tower_captures = $this->player_data->watch_tower_captures;
  }

  private function setMatchAward(){
    $this->match_award = $this->player_data->match_award;
  }

  private function setProtectionAllies(){
    $this->protection_allies = $this->player_data->protection_allies;
  }

  private function setSilencingEnemies(){
    $this->silencing_enemies = $this->player_data->silencing_enemies;
  }

  private function setRootingEnemies(){
    $this->rooting_enemies = $this->player_data->rooting_enemies;
  }

  private function setStunningEnemies(){
    $this->stunning_enemies = $this->player_data->stunning_enemies;
  }

  private function setClutchHeals(){
    $this->clutch_heals = $this->player_data->clutch_heals;
  }

  private function setEscapes(){
    $this->escapes = $this->player_data->escapes;
  }

  private function setVengeance(){
    $this->vengeance = $this->player_data->vengeance;
  }

  private function setOutnumberedDeaths(){
    $this->outnumbered_deaths = $this->player_data->outnumbered_deaths;
  }

  private function setTeamfightEscapes(){
    $this->teamfight_escapes = $this->player_data->teamfight_escapes;
  }

  private function setTeamfightHealing(){
    $this->teamfight_healing = $this->player_data->teamfight_healing;
  }

  private function setTeamfightDamageTaken(){
    $this->teamfight_damage_taken = $this->player_data->teamfight_damage_taken;
  }

  private function setTeamfightHeroDamage(){
    $this->teamfight_hero_damage = $this->player_data->teamfight_hero_damage;
  }

  private function setMultikill(){
    $this->multikill = $this->player_data->multikill;
  }

  private function setPhysicalDamage(){
    $this->physical_damage = $this->player_data->physical_damage;
  }

  private function setSpellDamage(){
    $this->spell_damage = $this->player_data->spell_damage;
  }

  private function setRegenGlobes(){
    $this->regen_globes = $this->player_data->regen_globes;
  }

  private function setFirstToTen(){
    $this->first_to_ten = $this->player_data->first_to_ten;
  }

  private function setTimeOnFire(){
    $this->time_on_fire = $this->player_data->time_on_fire;
  }






}
