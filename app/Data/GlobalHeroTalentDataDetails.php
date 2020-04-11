<?php
namespace App\Data;
use Illuminate\Support\Facades\DB;
use Session;

class GlobalHeroTalentDataDetails
{
  private $hero;
  private $timeframe;
  private $game_versions;
  private $game_type;
  private $player_league_tier;
  private $hero_league_tier;
  private $role_league_tier;
  private $game_map;
  private $hero_level;
  private $mirror;
  private $region;

  public static function instance($hero, $timeframe, $game_type, $player_league_tier,
                               $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                               $mirror, $region)
  {
    return new GlobalHeroTalentDataDetails($hero, $timeframe, $game_type, $player_league_tier,
                                 $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                                 $mirror, $region);
  }


  public function __construct($hero, $timeframe, $game_type, $player_league_tier,
                               $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                               $mirror, $region) {
    $this->timeframe = $timeframe;
    $this->game_type = $game_type;
    $this->hero = $hero;
    $this->player_league_tier = $player_league_tier;
    $this->hero_league_tier = $hero_league_tier;
    $this->role_league_tier = $role_league_tier;
    $this->game_map = $game_map;
    $this->hero_level = $hero_level;
    $this->mirror = $mirror;
    $this->region = $region;
  }

  public function getGlobalTalentDetailData(){
    $this->game_versions = \GlobalFunctions::instance()->getGameVersionsFromFilter($this->timeframe);

    $sub_query = \App\GlobalHeroTalentsDetails::Filters($this->hero, $this->game_versions, $this->game_type, $this->player_league_tier,
                                          $this->hero_league_tier, $this->role_league_tier, $this->game_map, $this->hero_level, $this->mirror, $this->region)
                   ->select('hero', 'win_loss', 'talent', 'global_hero_talents_details.level', DB::raw('SUM(games_played) as games_played'))
                   ->where('global_hero_talents_details.talent', '<>', '0')
                   ->groupBy('hero', 'sort', 'global_hero_talents_details.level', 'win_loss', 'global_hero_talents_details.talent')
                   ->orderBy('level', 'DESC')
                   ->orderBy('sort', 'DESC')
                   ->orderBy('talent', 'DESC')
                   ->orderBy('win_loss', 'DESC');

   $talent_details = \App\GlobalHeroTalents::select(
       'level',
       'talent',
       DB::raw('SUM(IF(win_loss = 1, games_played, 0)) AS wins'),
       DB::raw('SUM(IF(win_loss = 0, games_played, 0)) AS losses')
     )
     ->from(DB::raw('(' . $sub_query->toSql() . ') AS data'))
     ->groupBy('level', 'talent')
     ->mergeBindings($sub_query->getQuery())
     ->get();

    return $talent_details;
  }
}
