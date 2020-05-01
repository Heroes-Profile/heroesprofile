<?php
namespace App\Data;
use Illuminate\Support\Facades\DB;

class GlobalHeroStatMapData
{
  private $hero;
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
    return new GlobalHeroStatMapData($hero, $game_versions_minor, $game_type, $region, $game_map,
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

  public function getGlobalHeroStatMapData(){
    $global_map_data = \App\Models\GlobalHeroStats::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
                   ->join('maps', 'maps.map_id', '=', 'global_hero_stats.game_map')
                   ->select('maps.name as game_map', DB::raw('SUM(games_played) as games_played'))
                   ->groupBy('maps.name')
                   ->get();
      $total_map_games_played = array();
     for($i = 0; $i < count($global_map_data); $i++){
       $total_map_games_played[$global_map_data[$i]->game_map] = $global_map_data[$i]->games_played;
     }

    $sub_query = \App\Models\GlobalHeroStats::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror, $this->hero)
                   ->join('maps', 'maps.map_id', '=', 'global_hero_stats.game_map')
                   ->select('maps.name as game_map', 'win_loss', DB::raw('SUM(games_played) as games_played'))
                   ->groupBy('maps.name', 'win_loss');

    $global_map_data = \App\Models\GlobalHeroStats::select(
       'game_map',
       DB::raw('SUM(IF(win_loss = 1, games_played, 0)) AS wins'),
       DB::raw('SUM(IF(win_loss = 0, games_played, 0)) AS losses')
     )
     ->from(DB::raw('(' . $sub_query->toSql() . ') AS data'))
     ->mergeBindings($sub_query->getQuery())
     ->groupBy('game_map')
     ->get();

     $global_ban_data = $this->getHeroMapBans();


     for($i = 0; $i < count($global_map_data); $i++){
       $total_games = $total_map_games_played[$global_map_data[$i]->game_map] / 10;
       $global_map_data[$i]->bans = 0;
       $global_map_data[$i]->win_rate = 0;

       $global_map_data[$i]->games_played = $global_map_data[$i]->wins + $global_map_data[$i]->losses;
       if($global_map_data[$i]->games_played > 0){
         $global_map_data[$i]->win_rate =number_format(($global_map_data[$i]->wins / $global_map_data[$i]->games_played) * 100, 2);
       }

       $global_map_data[$i]->pick_rate = number_format(($global_map_data[$i]->games_played / $total_games) * 100, 2);

       if(array_key_exists($global_map_data[$i]->game_map, $global_ban_data)){
         $global_map_data[$i]->bans = $global_ban_data[$global_map_data[$i]->game_map];
       }
       $global_map_data[$i]->ban_rate = number_format(($global_map_data[$i]->bans / $total_games) * 100, 2);
       $global_map_data[$i]->popularity = number_format((($global_map_data[$i]->games_played + $global_map_data[$i]->bans) / $total_games)*100, 2);
     }
     return $global_map_data;
  }

  private function getHeroMapBans(){
    $global_ban_data = \App\Models\GlobalHeroBans::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
                      ->join('maps', 'maps.map_id', '=', 'global_hero_stats_bans.game_map')
                      ->select('name as game_map', DB::raw('SUM(bans) as games_banned'))
                      ->where('hero', $this->hero)
                      ->groupBy('name')
                      ->get();
    $return_data = array();
    for($i = 0; $i < count($global_ban_data); $i++){
      $return_data[$global_ban_data[$i]->game_map] = $global_ban_data[$i]->games_banned;
    }
    return $return_data;
  }
}
