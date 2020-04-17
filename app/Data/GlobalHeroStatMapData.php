<?php
namespace App\Data;
use Illuminate\Support\Facades\DB;
use Session;
use Cache;

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
    $sub_query = \App\Models\GlobalHeroStats::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror, $this->hero)
                   ->join('maps', 'maps.map_id', '=', 'global_hero_stats.game_map')
                   ->select(DB::raw('maps.name as game_map'), 'win_loss', DB::raw('SUM(games_played) as games_played'))
                   ->groupBy('maps.name', 'win_loss');

    $global_hero_data = \App\Models\GlobalHeroStats::select(
       'game_map',
       DB::raw('SUM(IF(win_loss = 1, games_played, 0)) AS wins'),
       DB::raw('SUM(IF(win_loss = 0, games_played, 0)) AS losses')
     )
     ->from(DB::raw('(' . $sub_query->toSql() . ') AS data'))
     ->mergeBindings($sub_query->getQuery())
     ->groupBy('game_map')
     ->get();
     return $global_hero_data;
  }
}
