<?php
namespace App\Data;
use Illuminate\Support\Facades\DB;
use Cache;

class GlobalStatData
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

  public static function instance($game_versions_minor, $game_type, $region, $game_map,
                                        $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror)
  {
    return new GlobalStatData($game_versions_minor, $game_type, $region, $game_map,
                                          $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror);
  }


  public function __construct($game_versions_minor, $game_type, $region, $game_map,
                                        $hero_level, $stat_type, $player_league_tier, $hero_league_tier, $role_league_tier, $mirror) {
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

  private function getHeroWinLosses(){
    $sub_query = \App\Models\GlobalHeroStats::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
                   ->select('name as hero', 'win_loss', DB::raw('SUM(games_played) as games_played'))
                   ->groupBy('hero', 'win_loss');

    $global_hero_data = \App\Models\GlobalHeroStats::select(
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
    $global_ban_data = \App\Models\GlobalHeroBans::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
                      ->join('heroes', 'heroes.id', '=', 'global_hero_stats_bans.hero')
                      ->select('name as hero', DB::raw('SUM(bans) as games_banned'))
                      ->groupBy('hero')
                      ->get();
    $return_data = array();
    for($i = 0; $i < count($global_ban_data); $i++){
      $return_data[$global_ban_data[$i]->hero] = $global_ban_data[$i]->games_banned;
    }
    return $return_data;
  }

  private function getHeroChange(){
    if(count($this->game_versions_minor) == 1
      && count($this->game_type) == 1
      && $this->game_type[0] != "br"
      && count($this->game_map) == 0
      && count($this->player_league_tier) == 0
      && count($this->hero_league_tier) == 0
      && count($this->role_league_tier) == 0
      && count($this->hero_level) == 0
      && count($this->region) == 0){
      $major_season_game_version = \App\Models\SeasonGameVersions::where('game_version', '>=', '2.43')
                                  ->select(DB::raw("DISTINCT(SUBSTRING_INDEX(game_version, '.', 2)) as game_version"))
                                  ->orderBy('game_version', 'DESC')
                                  ->get();
      $major_season_game_version = json_decode(json_encode($major_season_game_version),true);
      $found = 0;
      $timeframe = "";
      if(count($major_season_game_version) > 0){
        for($i = 0; $i < count($major_season_game_version); $i++){
          if($found){
            $timeframe = $major_season_game_version[$i]["game_version"];
            break;
          }
          if($major_season_game_version[$i]["game_version"] == $this->game_versions_minor[0]){
            $found = 1;
          }
        }
      }

      if(!$found){
        foreach (getAllMinorPatches() as $key => $value){
          if($found){
            $timeframe = $value;
            break;
          }
          if($value == $this->game_versions_minor[0]){
            $found = 1;
          }
        }
      }

      $change_data = \App\Models\GlobalHeroChange::Filters($timeframe, $this->game_type[0])
                        ->select('hero', 'win_rate')
                        ->get();
      $return_data = array();
      $heroes = Cache::remember('heroes_by_id_to_name', getCacheTimeGlobals(), function () {
          return getHeroesIDMap("id", "name");
      });

      for($i = 0; $i < count($change_data); $i++){
          if(array_key_exists($change_data[$i]->hero,$heroes)){
            $return_data[$heroes[$change_data[$i]->hero]] = $change_data[$i]->win_rate;
          }
      }
      return $return_data;
    }
  }

  private function combineData(){
    $global_hero_data = $this->getHeroWinLosses();
    $global_ban_data = $this->getHeroBans();
    $global_change_data = $this->getHeroChange();

    $total_games = ($global_hero_data->sum('wins') + $global_hero_data->sum('losses')) / 10;

    for($i = 0; $i < count($global_hero_data); $i++){

      $global_hero_data[$i]->games_played = $global_hero_data[$i]->wins + $global_hero_data[$i]->losses;
      if($global_hero_data[$i]->games_played){
        $global_hero_data[$i]->win_rate = $global_hero_data[$i]->wins / ($global_hero_data[$i]->wins + $global_hero_data[$i]->losses);
        $global_hero_data[$i]->pick_rate = $global_hero_data[$i]->games_played / $total_games;
      }else{
        $global_hero_data[$i]->win_rate = 0;
        $global_hero_data[$i]->pick_rate = 0;
      }

      if(!is_null($global_ban_data)){
        if(array_key_exists($global_hero_data[$i]->hero, $global_ban_data)){
          $global_hero_data[$i]->games_banned = $global_ban_data[$global_hero_data[$i]->hero];
          $global_hero_data[$i]->ban_rate = $global_ban_data[$global_hero_data[$i]->hero] / $total_games;
        }
      }else{
        $global_hero_data[$i]->games_banned = 0;
        $global_hero_data[$i]->ban_rate = 0;
      }

      if(!is_null($global_change_data)){
        if(array_key_exists($global_hero_data[$i]->hero, $global_change_data)){
          $global_hero_data[$i]->change = ($global_hero_data[$i]->win_rate * 100)  - $global_change_data[$global_hero_data[$i]->hero];
        }
      }else{
        $global_hero_data[$i]->change = 0;
      }


      if($global_hero_data[$i]->games_banned > 0){
        $global_hero_data[$i]->popularity = (($global_hero_data[$i]->games_banned + $global_hero_data[$i]->games_played) / $total_games) * 100;
      }else{
        $global_hero_data[$i]->popularity = ($global_hero_data[$i]->games_played / $total_games) * 100;
      }
      $global_hero_data[$i]->influence = round(($global_hero_data[$i]->win_rate - .5) * ($global_hero_data[$i]->pick_rate * 10000));


      //Maybe add to function later
      $global_hero_data[$i]->win_rate = number_format($global_hero_data[$i]->win_rate * 100, 2);
      $global_hero_data[$i]->change = number_format($global_hero_data[$i]->change, 2);
      $global_hero_data[$i]->popularity = number_format($global_hero_data[$i]->popularity, 2);
      $global_hero_data[$i]->pick_rate = number_format($global_hero_data[$i]->pick_rate * 100, 2);
      $global_hero_data[$i]->ban_rate = number_format($global_hero_data[$i]->ban_rate * 100, 2);
    }

    return $global_hero_data;
  }
  public function getGlobalHeroStatData(){
    return $this->combineData();
  }
}
