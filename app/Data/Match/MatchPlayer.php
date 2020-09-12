<?php
namespace App\Data\Match;

class MatchPlayer
{
  public $blizz_id;
  public $region;
  public $battletag;
  public $patreon;
  public $opt_out;
  public $hero;
  public $account_level;
  public $player_mmr;
  public $hero_mmr;
  public $role_mmr;

  public function __construct($blizz_id, $region) {
    $this->blizz_id = $blizz_id;
    $this->region = $region;
  }

  public function setBattletag($battletag){
    $this->battletag = \App\Models\Battletag::where('player_id', $battletag)->value('battletag');
  }
  public function getPatreon(){
    $this->patreon = \App\Models\Battletag::where('blizz_id', $this->blizz_id)->where('region', $this->region)->max('patreon');
  }

  public function getOptOut(){
    $this->opt_out = \App\Models\Battletag::where('blizz_id', $this->blizz_id)->where('region', $this->region)->max('opt_out');
  }

  public function setAccountLevel(){
    $this->account_level = \App\Models\Battletag::where('blizz_id', $this->blizz_id)->where('region', $this->region)->max('account_level');
  }

  public function setHero($hero_id){
    $this->hero = \App\Models\Hero::select('id', 'name', 'short_name')->where('id', $hero_id)->first();
  }

  public function setHeroLevel($hero_level, $mastery_taunt){
    switch ($mastery_taunt)
    {
      case 0:
        $this->hero->hero_level = strval($hero_level);
        break;
      case 1:
        $this->hero->hero_level = "15-25";
        break;
      case 2:
        $this->hero->hero_level = "25-50";
        break;
      case 3:
        $this->hero->hero_level = "50-75";
        break;
      case 4:
        $this->hero->hero_level = "75-100";
        break;
      case 5:
        $this->hero->hero_level = "100+";
        break;
      default:
        $this->hero->hero_level = 0;
    }
  }



  public function setPlayerMMR($mmr){
    $this->player_mmr = $mmr;
  }

  public function setHeroMMR($mmr){
    $this->hero_mmr = $mmr;
  }

  public function setRoleMMR($mmr){
    $this->role_mmr = $mmr;
  }
}
