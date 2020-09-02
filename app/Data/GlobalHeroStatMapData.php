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
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror, $this->hero)
                   ->join('maps', 'maps.map_id', '=', 'global_hero_stats.game_map')
                   ->selectRaw('maps.name as game_map, SUM(games_played) as games_played')
                   ->groupBy('maps.name');
                   print_r($global_map_data->toSql());
                   echo "<br>";
                   print_r($global_map_data->getBindings());
                   echo "<br>";

                   //->get();
return $this->game_type;
                   /*
     $total_map_games_played = array();
     for($i = 0; $i < count($global_map_data); $i++){
       $total_map_games_played[$global_map_data[$i]->game_map] = $global_map_data[$i]->games_played / 10;
     }

    $global_map_data = \App\Models\GlobalHeroStats::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror, $this->hero)
                   ->join('maps', 'maps.map_id', '=', 'global_hero_stats.game_map')
                   ->selectRaw('maps.name as game_map, win_loss, SUM(games_played) as games_played')
                   ->groupBy('maps.name', 'win_loss')
                   ->get();

     $global_ban_data = $this->getHeroMapBans();
     $return_data = array();
     $counter = 0;
     $prev_map = "";
     for($i = 0; $i < count($global_map_data); $i++){
       if($prev_map != "" & $prev_map != $global_map_data[$i]->game_map){
         $counter++;
       }
       $return_data[$counter]["game_map"] = $global_map_data[$i]->game_map;
       if($global_map_data[$i]->win_loss == 1){
         $return_data[$counter]["wins"] = $global_map_data[$i]->games_played;
       }else{
         $return_data[$counter]["losses"] = $global_map_data[$i]->games_played;
       }
       $prev_map = $global_map_data[$i]->game_map;
     }

     for($i = 0; $i < count($return_data); $i++){
       $return_data[$i]["games_played"] = $return_data[$i]["wins"] + $return_data[$i]["losses"];
       $return_data[$i]["bans"] = 0;
       $return_data[$i]["win_rate"] = 0;
       $return_data[$i]["pick_rate"] = 0;
       $return_data[$i]["ban_rate"] = 0;
       $return_data[$i]["popularity"] = 0;

       if($return_data[$i]["games_played"]){
         $return_data[$i]["win_rate"] = number_format(($return_data[$i]["wins"] / $return_data[$i]["games_played"]) * 100, 2);
         $return_data[$i]["pick_rate"] = number_format(($return_data[$i]["games_played"] / $total_map_games_played[$return_data[$i]["game_map"]]) * 100, 2);
       }

       if(array_key_exists($return_data[$i]["game_map"], $global_ban_data)){
         $return_data[$i]["bans"] = $global_ban_data[$return_data[$i]["game_map"]];
         $return_data[$i]["ban_rate"] = number_format(($return_data[$i]["bans"] / $total_map_games_played[$return_data[$i]["game_map"]]) * 100, 2);
       }
       $return_data[$i]["popularity"] = number_format((($return_data[$i]["games_played"] + $return_data[$i]["bans"]) / $total_map_games_played[$return_data[$i]["game_map"]]) * 100, 2);
     }

     return $return_data;
          */
  }

  private function getHeroMapBans(){
    $global_ban_data = \App\Models\GlobalHeroBans::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
                      ->join('maps', 'maps.map_id', '=', 'global_hero_stats_bans.game_map')
                      ->selectRaw('name as game_map, SUM(bans) as games_banned')
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
