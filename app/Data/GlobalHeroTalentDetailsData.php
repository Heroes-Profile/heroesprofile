<?php
namespace App\Data;

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
    $talent_details = \App\Models\GlobalHeroTalentsDetails::Filters($this->hero, $this->game_versions_minor, $this->game_type, $this->player_league_tier,
                                          $this->hero_league_tier, $this->role_league_tier, $this->game_map, $this->hero_level, $this->mirror, $this->region)
                   ->selectRaw('hero, win_loss, title, sort, global_hero_talents_details.level, SUM(games_played) as games_played')
                   ->groupBy('hero', 'sort', 'global_hero_talents_details.level', 'win_loss', 'title')
                   ->orderBy('level', 'ASC')
                   ->orderBy('sort', 'ASC')
                   ->get();


     $return_data = array();
     $counter = 0;
     $prev_level = 0;
     $prev_talent = "";
     for($i = 0; $i < count($talent_details); $i++){
       if($prev_level != 0 && $prev_level != $talent_details[$i]->level){
         $counter = 0;
       }else{
         if($prev_talent != "" && $prev_talent != $talent_details[$i]->title){
           $counter++;
         }
       }



       $return_data[$talent_details[$i]->level][$counter]["sort"] = $talent_details[$i]->sort;
       $return_data[$talent_details[$i]->level][$counter]["title"] = $talent_details[$i]->title;




       if($talent_details[$i]->win_loss == 1){
         $return_data[$talent_details[$i]->level][$counter]["wins"] = $talent_details[$i]->games_played;
       }else{
         $return_data[$talent_details[$i]->level][$counter]["losses"] = $talent_details[$i]->games_played;
       }
       $prev_level = $talent_details[$i]->level;
       $prev_talent = $talent_details[$i]->title;

     }
     /*
     print_r($sub_query->toSql());
     echo "<br>";
     print_r($sub_query->getBindings());
     echo "<br>";
     */


    foreach ($return_data as $level => $level_data){
      $level_games_played = array();
      $level_games_played[$level] = 0;
      for($i = 0; $i < count($level_data); $i++){

        $return_data[$level][$i]["games_played"] = $return_data[$level][$i]["wins"] + $return_data[$level][$i]["losses"];

        $return_data[$level][$i]["win_rate"] = 0;

        if($return_data[$level][$i]["games_played"]){
          $return_data[$level][$i]["win_rate"] = number_format(($return_data[$level][$i]["wins"] / $return_data[$level][$i]["games_played"]) * 100, 2);
        }

        $level_games_played[$level] += $return_data[$level][$i]["games_played"];


      }
      for($i = 0; $i < count($level_data); $i++){
        $return_data[$level][$i]["popularity"] = number_format(($return_data[$level][$i]["games_played"] / $level_games_played[$level]) * 100, 2);
      }

    }
    return $return_data;
  }
}
