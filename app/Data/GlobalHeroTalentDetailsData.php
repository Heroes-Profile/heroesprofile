<?php
namespace App\Data;
use Illuminate\Support\Facades\DB;
use Session;

class GlobalHeroTalentDetailsData
{
  private $hero;
  private $game_versions_minor;
  private $game_type;
  private $player_league_tier;
  private $hero_league_tier;
  private $role_league_tier;
  private $game_map;
  private $hero_level;
  private $mirror;
  private $region;

  public static function instance($hero, $game_versions_minor, $game_type, $player_league_tier,
                               $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                               $mirror, $region)
  {
    return new GlobalHeroTalentDetailsData($hero, $game_versions_minor, $game_type, $player_league_tier,
                                 $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                                 $mirror, $region);


  }


  public function __construct($hero, $game_versions_minor, $game_type, $player_league_tier,
                               $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                               $mirror, $region) {
    $this->game_versions_minor = $game_versions_minor;
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
    $sub_query = \App\Models\GlobalHeroTalentsDetails::Filters($this->hero, $this->game_versions_minor, $this->game_type, $this->player_league_tier,
                                          $this->hero_league_tier, $this->role_league_tier, $this->game_map, $this->hero_level, $this->mirror, $this->region)
                   ->select('hero', 'win_loss', 'title', 'sort', 'global_hero_talents_details.level', DB::raw('SUM(games_played) as games_played'))
                   ->groupBy('hero', 'sort', 'global_hero_talents_details.level', 'win_loss', 'title')
                   ->orderBy('level', 'DESC')
                   ->orderBy('sort', 'DESC')
                   ->orderBy('title', 'DESC')
                   ->orderBy('win_loss', 'DESC');

   $talent_details = \App\Models\GlobalHeroTalents::select(
       'level',
       'title',
       'sort',
       DB::raw('SUM(IF(win_loss = 1, games_played, 0)) AS wins'),
       DB::raw('SUM(IF(win_loss = 0, games_played, 0)) AS losses')
     )
     ->from(DB::raw('(' . $sub_query->toSql() . ') AS data'))
     ->groupBy('level', 'title', 'sort')
     ->mergeBindings($sub_query->getQuery())
     ->get();


     /*
     print_r($sub_query->toSql());
     echo "<br>";
     print_r($sub_query->getBindings());
     echo "<br>";
     */

    $level_games_played = array();

    for($i = 0; $i < count($talent_details); $i++){
      $talent_details[$i]->games_played = $talent_details[$i]->wins + $talent_details[$i]->losses;
      $talent_details[$i]->win_rate = 0;

      if($talent_details[$i]->games_played > 0){
        $talent_details[$i]->win_rate = number_format(($talent_details[$i]->wins / $talent_details[$i]->games_played) * 100,2);
      }
      if(!array_key_exists($talent_details[$i]->level, $level_games_played)){
        $level_games_played[$talent_details[$i]->level] = 0;
      }
      $level_games_played[$talent_details[$i]->level] += $talent_details[$i]->games_played;
    }
    for($i = 0; $i < count($talent_details); $i++){
      $talent_details[$i]->popularity = number_format(($talent_details[$i]->games_played / $level_games_played[$talent_details[$i]->level]) * 100,2);
    }

    return $talent_details;
  }
}
