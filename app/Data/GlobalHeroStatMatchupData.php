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
     $return_data = array();
     for($i = 0; $i < count($global_hero_matchup_data); $i++){
       $return_data[$global_hero_matchup_data[$i]->ally]["wins"] = $global_hero_matchup_data[$i]->wins;
       $return_data[$global_hero_matchup_data[$i]->ally]["losses"] = $global_hero_matchup_data[$i]->losses;
     }
     return $return_data;
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
     $return_data = array();
     for($i = 0; $i < count($global_hero_matchup_data); $i++){
       $return_data[$global_hero_matchup_data[$i]->enemy]["wins"] = $global_hero_matchup_data[$i]->wins;
       $return_data[$global_hero_matchup_data[$i]->enemy]["losses"] = $global_hero_matchup_data[$i]->losses;
     }
     return $return_data;
  }

  public function getGlobalHeroStatMatchupData(){
    $return_data_ally = $this->getGlobalHeroStatAllyData();
    $return_data_enemy = $this->getGlobalHeroStatEnemyData();

    $heroes = \App\Models\Hero::select('name', 'id')->get();

    $return_data = array();
    $return_data_counter = 0;

    for($i = 0; $i < count($heroes); $i++){
      if($heroes[$i]->id != $this->hero){
        $return_data[$return_data_counter]["hero"] = $heroes[$i]->name;
        if(!array_key_exists($heroes[$i]->name, $return_data_ally)){
          $return_data_ally[$heroes[$i]->name]["wins"] = 0;
          $return_data_ally[$heroes[$i]->name]["losses"] = 0;
        }

        if(!array_key_exists("wins", $return_data_ally[$heroes[$i]->name])){
          $return_data_ally[$heroes[$i]->name]["wins"] = 0;
        }

        if(!array_key_exists("losses", $return_data_ally[$heroes[$i]->name])){
          $return_data_ally[$heroes[$i]->name]["losses"] = 0;
        }

        $return_data[$return_data_counter]["games_played_as_ally"] = $return_data_ally[$heroes[$i]->name]["wins"] + $return_data_ally[$heroes[$i]->name]["losses"];
        $return_data[$return_data_counter]["win_rate_as_ally"] = number_format(($return_data_ally[$heroes[$i]->name]["wins"] /   $return_data[$return_data_counter]["games_played_as_ally"]) *100, 2);


        if(!array_key_exists($heroes[$i]->name, $return_data_enemy)){
          $return_data_enemy[$heroes[$i]->name]["wins"] = 0;
          $return_data_enemy[$heroes[$i]->name]["losses"] = 0;
        }

        if(!array_key_exists("wins", $return_data_enemy[$heroes[$i]->name])){
          $return_data_enemy[$heroes[$i]->name]["wins"] = 0;
        }

        if(!array_key_exists("losses", $return_data_enemy[$heroes[$i]->name])){
          $return_data_enemy[$heroes[$i]->name]["losses"] = 0;
        }

        $return_data[$return_data_counter]["games_played_as_enemy"] = $return_data_enemy[$heroes[$i]->name]["wins"] + $return_data_enemy[$heroes[$i]->name]["losses"];
        $return_data[$return_data_counter]["win_rate_as_enemy"] = number_format(100 - ($return_data_enemy[$heroes[$i]->name]["wins"] /   $return_data[$return_data_counter]["games_played_as_enemy"]) *100, 2);



        $return_data_counter++;
      }

    }
    return $return_data;
  }
}
