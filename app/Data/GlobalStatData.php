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
    $global_hero_data = \App\Models\GlobalHeroStats::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
                   ->selectRaw('name as hero, win_loss, SUM(games_played) as games_played')
                   ->groupBy('hero', 'win_loss')
                   ->get();


    $return_data = array();
    for($i = 0; $i < count($global_hero_data); $i++){
      if($global_hero_data[$i]->win_loss == 1){
        $return_data[$global_hero_data[$i]->hero]["wins"] = $global_hero_data[$i]->games_played;
      }else{
        $return_data[$global_hero_data[$i]->hero]["losses"] = $global_hero_data[$i]->games_played;
      }
    }

    return $return_data;
  }

  private function getHeroBans(){
    $global_ban_data = \App\Models\GlobalHeroBans::Filters($this->game_versions_minor, $this->game_type, $this->region, $this->game_map,
                                          $this->hero_level, $this->player_league_tier, $this->hero_league_tier, $this->role_league_tier, $this->mirror)
                      ->join('heroes', 'heroes.id', '=', 'global_hero_stats_bans.hero')
                      ->selectRaw('name as hero, SUM(bans) as games_banned')
                      ->groupBy('hero')
                      ->get();
    $return_data = array();
    for($i = 0; $i < count($global_ban_data); $i++){
      $return_data[$global_ban_data[$i]->hero] = round($global_ban_data[$i]->games_banned);
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
      $major_season_game_version = array_keys(getFilterVersions());
      $found = 0;
      $timeframe = "";
      if(count($major_season_game_version) > 0){
        for($i = 0; $i < count($major_season_game_version); $i++){
          if($found){
            $timeframe = $major_season_game_version[$i];
            break;
          }
          if($major_season_game_version[$i] == $this->game_versions_minor[0]){
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
      $heroes = getHeroesIDMap("id", "name");

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

    $total_games = 0;
    foreach ($global_hero_data as $hero => $data){
      if(!array_key_exists("wins",$data )){
        $data["wins"] = 0;
      }

      if(!array_key_exists("losses",$data )){
        $data["losses"] = 0;
      }

      $total_games += $data["wins"] + $data["losses"];
    }
    $total_games /= 10;
    $return_data = array();
    $counter = 0;
    foreach ($global_hero_data as $hero => $data)
    {
      $return_data[$counter]["hero"] = $hero;
      $return_data[$counter]["wins"] = $data["wins"];
      $return_data[$counter]["losses"] = $data["losses"];

      $return_data[$counter]["games_played"] = $data["wins"] + $data["losses"];

      if($return_data[$counter]["games_played"]){
        $return_data[$counter]["win_rate"] = $data["wins"] / ($data["wins"] + $data["losses"]);
        $return_data[$counter]["pick_rate"] = $return_data[$counter]["games_played"] / $total_games;
      }else{
        $return_data[$counter]["win_rate"] = 0;
        $return_data[$counter]["pick_rate"] = 0;
      }

      if(isset($global_ban_data[$hero])){
        $return_data[$counter]["games_banned"] = $global_ban_data[$hero];
        $return_data[$counter]["ban_rate"] = $global_ban_data[$hero] / $total_games;
      }else{
        $return_data[$counter]["games_banned"] = 0;
        $return_data[$counter]["ban_rate"] = 0;
      }

      if(!is_null($global_change_data)){
        if(array_key_exists($hero, $global_change_data)){
          $return_data[$counter]["change"] = ($return_data[$counter]["win_rate"] * 100)  - $global_change_data[$hero];
        }
      }else{
        $return_data[$counter]["change"] = 0;
      }


      if(isset($return_data[$counter]["games_banned"])){
        $return_data[$counter]["popularity"] = (($return_data[$counter]["games_banned"] + $return_data[$counter]["games_played"]) / $total_games) * 100;
      }else{
        $return_data[$counter]["popularity"] = ($return_data[$counter]["games_played"] / $total_games) * 100;
      }
      $return_data[$counter]["influence"] = round(($return_data[$counter]["win_rate"] - .5) * ($return_data[$counter]["pick_rate"] * 10000));


      //Maybe add to function later
      $return_data[$counter]["win_rate_confidence"] = number_format((1.96 * sqrt((($return_data[$counter]["win_rate"]*(1-$return_data[$counter]["win_rate"]))/$return_data[$counter]["games_played"]))) * 100, 2);




      $return_data[$counter]["win_rate"] = number_format($return_data[$counter]["win_rate"] * 100, 2);
      $return_data[$counter]["change"] = number_format($return_data[$counter]["change"], 2);
      $return_data[$counter]["popularity"] = number_format($return_data[$counter]["popularity"], 2);
      $return_data[$counter]["pick_rate"] = number_format($return_data[$counter]["pick_rate"] * 100, 2);

      if(isset($return_data[$counter]["ban_rate"])){
        $return_data[$counter]["ban_rate"] = number_format($return_data[$counter]["ban_rate"] * 100, 2);
      }else{
        $return_data[$counter]["ban_rate"] = 0;
      }


      $counter++;
    }

    return $return_data;
  }
  public function getGlobalHeroStatData(){
    return $this->combineData();
  }
}
