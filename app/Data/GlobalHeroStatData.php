<?php
namespace App\Data;
use Illuminate\Support\Facades\DB;

/*
use Illuminate\Support\Facades\DB;
use Cache;
use Session;
use App\Battletag;
use App\LeagueBreakdown;
use App\LeagueTier;
use DateTime;
*/

class GlobalHeroStatData
{
  private $game_version;
  private $game_type;
  private $player_league_tier;
  private $hero_league_tier;
  private $role_league_tier;
  private $game_map;
  private $hero_level;
  private $mirror;
  private $region;

  public static function instance($game_version, $game_type, $player_league_tier,
                               $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                               $mirror, $region)
  {
    return new GlobalHeroStatData($game_version, $game_type, $player_league_tier,
                                 $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                                 $mirror, $region);
  }


  public function __construct($game_version, $game_type, $player_league_tier,
                               $hero_league_tier, $role_league_tier, $game_map, $hero_level,
                               $mirror, $region) {
    $this->game_version = $game_version;
    $this->game_type = $game_type;
    $this->player_league_tier = $player_league_tier;
    $this->hero_league_tier = $hero_league_tier;
    $this->role_league_tier = $role_league_tier;
    $this->game_map = $game_map;
    $this->hero_level = $hero_level;
    $this->mirror = $mirror;
    $this->region = $region;
  }

  private function getHeroWinLosses(){
    $sub_query = \App\GlobalHeroStats::Filters($this->game_version, $this->game_type, $this->player_league_tier,
                                          $this->hero_league_tier, $this->role_league_tier, $this->game_map, $this->hero_level, $this->mirror, $this->region)
                   ->select('hero', 'win_loss', DB::raw('SUM(games_played) as games_played'))
                   ->groupBy('hero', 'win_loss');

    $global_hero_data = \App\GlobalHeroStats::select(
        'hero',
        DB::raw('SUM(IF(win_loss = 1, games_played, 0)) AS wins'),
        DB::raw('SUM(IF(win_loss = 0, games_played, 0)) AS losses'),
        DB::raw('0 as games_banned')
      )
      ->from(DB::raw('(' . $sub_query->toSql() . ') AS data'))
      ->mergeBindings($sub_query->getQuery())
      ->groupBy('hero')
      ->get();

      return $global_hero_data;
  }

  private function getHeroBans(){
    $global_ban_data = \App\GlobalHeroBans::Filters(array('2.49.2.77981'), array('1', '5'), array(),
                                          array(), array(), array(), array(), array())
                      ->select('hero', DB::raw('SUM(bans) as games_banned'))
                      ->groupBy('hero')
                      ->get();
    return $global_ban_data;
  }

  private function getHeroChange(){
  }

  private function combineData(){
    $global_hero_data = $this->getHeroWinLosses();
    $global_ban_data = $this->getHeroBans();
    $global_change_data = $this->getHeroChange();

    $total_games = ($global_hero_data->sum('wins') + $global_hero_data->sum('losses')) / 10;
    $total_bans = $global_ban_data->sum('games_banned');

    for($i = 0; $i < count($global_hero_data); $i++){
      $global_hero_data[$i]->games_played = $global_hero_data[$i]->wins + $global_hero_data[$i]->losses;
      if($global_hero_data[$i]->games_played){
        $global_hero_data[$i]->win_rate = ($global_hero_data[$i]->wins / ($global_hero_data[$i]->wins + $global_hero_data[$i]->losses)) * 100;
        $global_hero_data[$i]->pick_rate = ($global_hero_data[$i]->games_played / $total_games) * 100;
      }else{
        $global_hero_data[$i]->win_rate = 0;
        $global_hero_data[$i]->pick_rate = 0;
      }

      foreach ($global_ban_data as $ban_data) {
        if($ban_data->hero == $global_hero_data[$i]->hero){
          $global_hero_data[$i]->games_banned = $ban_data->games_banned;
          break;
        }
      }

      if($global_hero_data[$i]->games_banned > 0){
        $global_hero_data[$i]->popularity = ($global_hero_data[$i]->games_played / $total_games) * 100;
      }else{
        $global_hero_data[$i]->popularity = (($global_hero_data[$i]->games_banned + $global_hero_data[$i]->games_played) / $total_games) * 100;
      }


      //$return_data[$i]["influence"] = round(($return_data[$i]["win_rate_influence"] - .5) * ($return_data[$i]["adjusted_pick_rate"] * 10000));
    }

    return $global_hero_data;
  }
  public function getGlobalHeroStatData(){
    return $this->combineData();
  }
}
