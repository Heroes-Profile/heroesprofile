<?php
namespace App\Data;
use Illuminate\Support\Facades\DB;

class GlobalHeroStatMatchupData
{
  private $game_versions_minor;
  private $game_type;
  private $region;
  private $game_map;
  private $hero_level;
  private $stat_type;
  private $player_league_tier;
  private $hero_league_tier;
  private $role_league_tier;
  private $mirror;

  public static function instance($hero, $game_versions_minor, $game_type, $region, $game_map,
                                        $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror)
  {
    return new GlobalHeroStatMatchupData($hero, $game_versions_minor, $game_type, $region, $game_map,
                                          $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
  }


  public function __construct($hero, $game_versions_minor, $game_type, $region, $game_map,
                                        $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror) {
    $this->hero = $hero;
    $this->game_versions_minor = $game_versions_minor;
    $this->game_type = $game_type;
    $this->region = $region;
    $this->game_map = $game_map;
    $this->hero_level = $hero_level;
    $this->stat_type = $stat_type;
    $this->player_league_tier = $player_league_tier;
    $this->hero_league_tier = $hero_league_tier;
    $this->role_league_tier = $role_league_tier;
    $this->mirror = $mirror;
  }
  private function getGlobalHeroStatAllyData(){
    $sub_query = \App\Models\GlobalHeroMatchupsAlly::Filters($this->hero, $this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
                   ->join('heroes', 'heroes.id', '=', 'global_hero_matchups_ally.ally')
                   ->select(DB::raw('name as ally'), 'win_loss', DB::raw('SUM(games_played) as games_played'))
                   ->groupBy('name', 'win_loss');

    $global_hero_matchup_data = \App\Models\GlobalHeroMatchupsEnemy::select(
       'ally',
       DB::raw('SUM(IF(win_loss = 1, games_played, 0)) AS wins'),
       DB::raw('SUM(IF(win_loss = 0, games_played, 0)) AS losses')
     )
     ->from(DB::raw('(' . $sub_query->toSql() . ') AS data'))
     ->mergeBindings($sub_query->getQuery())
     ->groupBy('ally')
     ->get();
     return $global_hero_matchup_data;
  }

  private function getGlobalHeroStatEnemyData(){
    $sub_query = \App\Models\GlobalHeroMatchupsEnemy::Filters($this->hero, $this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
                   ->join('heroes', 'heroes.id', '=', 'global_hero_matchups_enemy.enemy')
                   ->select(DB::raw('name as enemy'), 'win_loss', DB::raw('SUM(games_played) as games_played'))
                   ->groupBy('name', 'win_loss');

    $global_hero_matchup_data = \App\Models\GlobalHeroMatchupsEnemy::select(
       'enemy',
       DB::raw('SUM(IF(win_loss = 1, games_played, 0)) AS wins'),
       DB::raw('SUM(IF(win_loss = 0, games_played, 0)) AS losses')
     )
     ->from(DB::raw('(' . $sub_query->toSql() . ') AS data'))
     ->mergeBindings($sub_query->getQuery())
     ->groupBy('enemy')
     ->get();
     return $global_hero_matchup_data;
  }

  public function getGlobalHeroStatMatchupData(){
    $return_data_ally = $this->getGlobalHeroStatAllyData();

    for($i = 0; $i < count($return_data_ally); $i++){
      $return_data_ally[$i]->games_played = $return_data_ally[$i]->wins + $return_data_ally[$i]->losses;
      $return_data_ally[$i]->win_rate = number_format(($return_data_ally[$i]->wins / $return_data_ally[$i]->games_played) * 100, 2);
    }

    $return_data_enemy = $this->getGlobalHeroStatEnemyData();

    print_r(json_encode($return_data_enemy, true));
  }
}
