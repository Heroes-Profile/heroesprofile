<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Cache;
use Session;

class HeroController extends Controller
{
  protected $data;
  private $timeframe_type = array();
  private $timeframe = array();
  private $game_type = array();
  private $player_league_tier = array();
  private $hero_league_tier = array();
  private $role_league_tier = array();
  private $game_map =  array();
  private $hero_level = array();
  private $hero = array();
  private $stat_type = array();
  private $role = array();
  private $build_type = "Popular";
  private $talent_levels = ["level_one", "level_four", "level_seven", "level_ten", "level_thirteen", "level_sixteen", "level_twenty"];
private $maps = array();
  // Function to get values from post, check for values, and set default values
  private function setValues($fieldName, $default, $post, $key = 'key'){ //

    //$this->setValues('timeframe_type', "major", $post, 'key');
    if(isset($post[$fieldName]) && $post[$fieldName] != ""){
      if(is_string($post[$fieldName])){
        $this->{$fieldName} = explode(',', $post[$fieldName]);
      }elseif(is_array($post[$fieldName])){
        $this->{$fieldName} = $post[$fieldName];
        if(isset($this->{$fieldName}[0][$key])){
          for($i = 0; $i < count($this->{$fieldName}); $i++){
            $this->{$fieldName}[$i] = $this->{$fieldName}[$i][$key];
          }
        }
      }else{
        $this->{$fieldName} = $post[$fieldName];
        for($i = 0; $i < count($this->{$fieldName}); $i++){
          $this->{$fieldName}[$i] = $this->{$fieldName}[$i][$key];
        }
      }
      asort($this->{$fieldName});
      return true;
    }else{
      if($default){
        $this->{$fieldName} = (array)$default;
        asort($this->{$fieldName});
      }
      return false;
    }
  }

  public function show(){
    $global = \GlobalFunctions::instance();

    return 
    [
      'dataurl' => '/get_heroes_stats_table_data', // URL used for calling the table data
      'title' => 'Global Win Rates', // Page title
      'paragraph' => 'Hero win rates based on differing increments, stat types, game type, or league tier.', // Summary paragraph
      'tableheading' => 'Win Rates', // Table heading
      'timeframe_type' => [
          "key" => "timeframe_type",
          "name" => "Timeframe Type",
          "type" => "radio",
          "description" => "Choose a timeframe",
          "options" =>  array(
              [
                "key" => "major",
                "value" => "major"
              ],
              [
                "key" => "minor",
                "value" => "minor",
              ]
            ),
      ],
      'major_patch' => [
          "key" => "major_patch",
          "name" => "Timeframe",
          "type" => "multiselect",
          "description" => "Major patches",
          "conditional_field" => "timeframe_type",
          "conditional_value" => "major",
          // "options" => $global->convertToFilter(Session::get('all_major_patch')),
      ],
      'minor_patch' => [
          "key" => "minor_patch",
          "name" => "Timeframe",
          "type" => "multiselect",
          "description" => "Minor patches",
          "conditional_field" => "timeframe_type",
          "conditional_value" => "minor",
          // "options" => $global->convertToFilter(Session::get('all_minor_patch'))
      ],
      'primaryfields' => array(
        [
            "key" => "timeframe_type",
            "name" => "Timeframe Type",
            "type" => "radio",
            "description" => "Choose a timeframe",
            "options" =>  array(
                [
                  "key" => "major",
                  "value" => "major"
                ],
                [
                  "key" => "minor",
                  "value" => "minor",
                ]
              ),
        ],
        [
            "key" => "major_patch",
            "name" => "Timeframe",
            "type" => "multiselect",
            "description" => "Major patches",
            "conditional_field" => "timeframe_type",
            "conditional_value" => "major",
            "options" => $global->convertToFilter(Session::get('all_major_patch')),
        ],
        [
            "key" => "minor_patch",
            "name" => "Timeframe",
            "type" => "multiselect",
            "description" => "Minor patches",
            "conditional_field" => "timeframe_type",
            "conditional_value" => "minor",
            "options" => $global->convertToFilter(Session::get('all_minor_patch'))
        ],
        [
            "key" => "game_type",
            "name" => "Game Type",
            "type" => "checkbox",
            "description" => "",
            "options" => array(
                [
                  "key" => "Quick Match",
                  "value" => "Quick Match",
                  "text" => "Quick Match",
                  "icon" => "/images/role-icons.png"
                ],
                [
                  "key" => "Unranked Draft",
                  "value" => "Unranked Draft",
                  "text" => "Unranked Draft",
                  "icon" => "/images/role-icons.png"
                ],
                [
                  "key" => "Storm League",
                  "value" => "Storm League",
                  "text" => "Storm League",
                  "icon" => "/images/role-icons.png"
                ],
                [
                  "key" => "Brawl",
                  "value" => "Brawl",
                  "text" => "Brawl",
                  "icon" => "/images/role-icons.png"
                ]
              ),
        ]
      ),
      'secondaryfields' => array(
        [
            "key" => "game_map",
            "name" => "Map",
            "type" => "multiselect",
            "description" => "",
            "options" => Session::get('maps_by_name_filter_format'),
        ],
        [
            "key" => "player_league_tier",
            "name" => "Player League Tier",
            "type" => "checkbox",
            "description" => "",
            "options" => $global->convertToFilter($global->getLeagueTiersByName()),
        ],
        [
            "key" => "hero_level",
            "name" => "Hero Level",
            "type" => "checkbox",
            "description" => "The player's hero level when playing the game.",
            "options" => $global->convertToFilter($global->getHerolevels()),
        ],
        [
            "key" => "role",
            "name" => "Role",
            "type" => "checkbox",
            "description" => "Role",
          //  "options" => $global->convertToFilter(Session::get('role_names')),
          "options" => array(
            [ "key"=> "Bruiser", "value"=> "Bruiser", "text"=> "Bruiser", "icon"=> "/images/roles/bruiser.PNG" ],
            [ "key"=> "Healer", "value"=> "Healer", "text"=> "Healer", "icon" => "/images/roles/healer.PNG" ],
            [ "key"=> "Melee Assassin", "value"=> "Melee Assassin", "text"=> "Melee Assassin" , "icon" => "/images/roles/melee assassin.PNG"],
            [ "key"=> "Ranged Assassin", "value"=> "Ranged Assassin", "text"=> "Ranged Assassin", "icon" => "/images/roles/ranged assassin.PNG" ],
            ["key"=> "Support", "value"=> "Support", "text"=> "Support", "icon" => "/images/roles/support.PNG" ],
            [ "key"=> "Tank", "value"=> "Tank", "text"=> "Tank", "icon" => "/images/roles/tank.PNG" ]

          )

        ],
      ),

      'rawfields' => [
        "timeframe_type" => array(
            [
              "key" => "major",
              "value" => "major"
            ],
            [
              "key" => "minor",
              "value" => "minor",
            ]
          ),
        "major_patch" =>  $global->convertToFilter(Session::get('all_major_patch')),  //conditional on whether timeframe type is equal to major
        "minor_patch" =>  $global->convertToFilter(Session::get('all_minor_patch')),  //conditional on whether timeframe type is equal to minor
        "game_type" => array(
            [
              "key" => "Quick Match",
              "value" => 1,
              "text" => "Quick Match",
              "icon" => ""
            ],
            [
              "key" => "Unranked Draft",
              "value" => 2,
              "text" => "Unranked Draft",
              "icon" => ""
            ],
            [
              "key" => "Storm League",
              "value" => 5,
              "text" => "Storm League",
              "icon" => ""
            ],
            [
              "key" => "Brawl",
              "value" => -1,
              "text" => "Brawl",
              "icon" => ""
            ]
          ),
        "game_map" => $global->convertToFilter(Session::get('maps_by_name')),
        "player_league_tier" => $global->convertToFilter($global->getLeagueTiersByName()),
        "role_league_tier" => $global->convertToFilter($global->getLeagueTiersByName()),
        "hero_league_tier" => $global->convertToFilter($global->getLeagueTiersByName()),
        "type" => $global->convertToFilter(array_flip(Session::get('stat_columns'))),
        "hero_level" => $global->convertToFilter($global->getHerolevels()),
        "role" => array([ "key"=> "Bruiser", "value"=> "Bruiser", "text"=> "Bruiser", "icon"=> "/images/roles/bruiser.PNG" ],
        [ "key"=> "Healer", "value"=> "Healer", "text"=> "Healer", "icon" => "/images/roles/healer.PNG" ],
        [ "key"=> "Melee Assassin", "value"=> "Melee Assassin", "text"=> "Melee Assassin" , "icon" => "/images/roles/melee assassin.PNG"],
        [ "key"=> "Ranged Assassin", "value"=> "Ranged Assassin", "text"=> "Ranged Assassin", "icon" => "/images/roles/ranged assassin.PNG" ],
        ["key"=> "Support", "value"=> "Support", "text"=> "Support", "icon" => "/images/roles/support.PNG" ],
        [ "key"=> "Tank", "value"=> "Tank", "text"=> "Tank", "icon" => "/images/roles/tank.PNG" ]),
        "hero" => $global->convertToFilter(Session::get('heroes_name_to_short')) // I need to get the hero's short name in this array, too
        //  "key" = hero_name
        //  "value" = short_name
      /*  "hero" => $global->convertToFilter(Session::get('heroes_by_name')),*/
      ],
    ];
  }

  public function profile(){
    return view('hero/profile');
  }

  public function getFields(Request $request){
    $return_data = array();
    $heroes = array();

    $hero_array = Session::get("heroes_by_name");
    $hero_names = array_keys($hero_array);
    $hero_ids = array_values($hero_array);


    $heroes["options"] = $hero_names;
    $heroes["inputtype"] = "select";
    $heroes["multiselect"] = false;
    $heroes["inputname"] = "Hero";

    $game_map_array = Session::get("maps_by_name");
    $map_names = array_keys($game_map_array);
    $map_ids = array_values($game_map_array);

    $maps = array();
    $maps["options"] = $map_names;
    $maps["inputtype"] = "select";
    $maps["multiselect"] = false;
    $maps["inputname"] = "Map";
    /* $maps = new stdClass();

    $heroes->names = ["Battlefield of Eternity","Other Map"];
    $hereos->inputtype = "select";
    $heroes->multiselect = false;*/

    array_push($return_data, $heroes);
    array_push($return_data, $maps);
    return json_encode($return_data);

  }

  public function getHeroStatsTableData(Request $request){
    $post = $request->post();
    $post = $post["params"]["data"];

    $this->setValues('timeframe_type', "minor", $post, 'key');
    $this->setValues('timeframe', Session::get("minor_patch_latest"), $post, 'key');
    if($this->setValues('game_type', array(Session::get("default_game_mode_id")), $post, 'key')){
      for($i = 0; $i < count($this->game_type); $i++){
        $this->game_type[$i] = Session::get("game_types_by_name")[$this->game_type[$i]];
      }
    }
    $this->setValues('hero', "", $post, 'key');
    $this->setValues('stat_type', "", $post, 'key');
    $this->setValues('hero_level', array(), $post, 'key');
    $this->setValues('role', array(), $post, 'key');
    if($this->setValues('game_map', array(), $post, 'key')){
      for($i = 0; $i < count($this->game_map); $i++){
        $this->game_map[$i] = Session::get("maps_by_name")[$this->game_map[$i]];
      }
      asort($this->game_map);
    }


    if($this->setValues('player_league_tier', array(), $post, 'key')){
      for($i = 0; $i < count($this->player_league_tier); $i++){
        $this->player_league_tier[$i] = Session::get("league_tiers_by_name")[$this->player_league_tier[$i]];
      }
      asort($this->player_league_tier);
    }

    if($this->setValues('hero_league_tier', array(), $post, 'key')){
      for($i = 0; $i < count($this->hero_league_tier); $i++){
        $this->hero_league_tier[$i] = Session::get("league_tiers_by_name")[$this->hero_league_tier[$i]];
      }
      asort($this->hero_league_tier);
    }

    if($this->setValues('role_league_tier', array(), $post, 'key')){
      for($i = 0; $i < count($this->role_league_tier); $i++){
        $this->role_league_tier[$i] = Session::get("league_tiers_by_name")[$this->role_league_tier[$i]];
      }
      asort($this->role_league_tier);
    }


    //remove later
    if(count($this->timeframe) == 0 || !isset($this->timeframe)){
      $this->timeframe = array();
      $this->timeframe[0] = '2.49.1.77692';
    }

    $page = "GlobalHeroStats";
    $cache =  $page .
              "|" . implode(",", $this->timeframe_type) .
              "|" . implode(",", $this->timeframe) .
              "|" . implode(",", $this->stat_type) .
              "|" . implode(",", $this->hero_level) .
              "|" . implode(",", $this->game_type) .
              "|" . implode(",", $this->game_map) .
              "|"  . implode(",", $this->player_league_tier) .
              "|"  . implode(",", $this->hero_league_tier) .
              "|"  . implode(",", $this->role_league_tier);

    //$return_data = Cache::remember($cache, 1, function () use ($request){
    $return_data = Cache::remember($cache, 900, function () use ($request){

      $maps = Session::get("maps_by_name");
      $query = DB::table('heroesprofile.global_hero_stats');

      if(count($this->timeframe) != 0){
        if($this->timeframe_type[0] == "major"){
          $patches_array = array();
          for($i = 0; $i < count($this->timeframe); $i++){
            if(count($patches_array) == 0){
              $patches_array = Session::get('major_to_minor_patch_mapping')[$this->timeframe[$i]];
            }else{
              array_merge($patches_array, Session::get('major_to_minor_patch_mapping')[$this->timeframe[$i]]);
            }
          }
          $query->whereIn('game_version', $patches_array);
        }else{
          $query->whereIn('game_version', $this->timeframe);
        }
      }
      if(count($this->game_type) != 0){
        $query->whereIn('game_type', $this->game_type);
      }

      if(count($this->player_league_tier) != 0){
        $query->whereIn('league_tier', $this->player_league_tier);
      }

      if(count($this->hero_league_tier) != 0){
        $query->whereIn('hero_league_tier', $this->hero_league_tier);
      }

      if(count($this->role_league_tier) != 0){
        $query->whereIn('role_league_tier', $this->role_league_tier);
      }

      if(count($this->game_map) != 0){
        $query->whereIn('game_map', $this->game_map);
      }

      if(count($this->hero_level) != 0){
        $query->whereIn('hero_level', $this->hero_level);
      }

      $query->join('heroes', 'heroes.id', '=', 'global_hero_stats.hero');
      if(count($this->stat_type) != 0){
        $query->select('heroes.name', 'global_hero_stats.win_loss', DB::raw('SUM(games_played) as games_played'), DB::raw('SUM(' . $this->stat_type[0] . ') as ' . $this->stat_type[0]));
      }else{
        $query->select('heroes.name', 'global_hero_stats.win_loss', DB::raw('SUM(games_played) as games_played'));
      }
      $query->groupBy('name', 'win_loss');
      $data = $query->get();

      //print_r($query->toSql());
      //print_r($query->getBindings());

      $data = json_decode(json_encode($data),true);

      $return_data = array();
      $counter = 0;
      $prev_name = "";

      $total_games = 0;
      for($i = 0; $i < count($data); $i++){
        if($prev_name != "" && $prev_name != $data[$i]["name"]){
          $counter++;
        }

        $return_data[$counter]["name"]["hero_name"] = $data[$i]["name"];
        $return_data[$counter]["name"]["short_name"] = Session::get("heroes_name_to_short")[$data[$i]["name"]];
        $return_data[$counter]["name"]["role"] = Session::get("roles_by_hero_name")[$data[$i]["name"]];
        $return_data[$counter]["name"]["type"] = Session::get("types_by_hero_name")[$data[$i]["name"]];



        if(!array_key_exists("games_played",$return_data[$counter])){
          $return_data[$counter]["games_played"] = $data[$i]["games_played"];
        }else{
          $return_data[$counter]["games_played"] += $data[$i]["games_played"];
        }

        if($data[$i]["win_loss"] == 1){
          $return_data[$counter]["wins"] = floatval($data[$i]["games_played"]);
        }else{
          $return_data[$counter]["losses"] = floatval($data[$i]["games_played"]);
        }



        if(count($this->stat_type) != 0){
          if(!array_key_exists($this->stat_type[0],$return_data[$counter])){
            $return_data[$counter][$this->stat_type[0]] = $data[$i][$this->stat_type[0]];
          }else{
            $return_data[$counter][$this->stat_type[0]] += $data[$i][$this->stat_type[0]];
          }
        }

        $prev_name = $data[$i]["name"];
        $total_games += $data[$i]["games_played"];
      }
      $query = DB::table('heroesprofile.global_hero_stats_bans');

      if(count($this->timeframe) != 0){
        if($this->timeframe_type[0] == "major"){
          $patches_array = array();
          for($i = 0; $i < count($this->timeframe); $i++){
            if(count($patches_array) == 0){
              $patches_array = Session::get('major_to_minor_patch_mapping')[$this->timeframe[$i]];
            }else{
              array_merge($patches_array, Session::get('major_to_minor_patch_mapping')[$this->timeframe[$i]]);
            }
          }
          $query->whereIn('game_version', $patches_array);
        }else{
          $query->whereIn('game_version', $this->timeframe);
        }
      }
      if(count($this->game_type) != 0){
        $query->whereIn('game_type', $this->game_type);
      }

      if(count($this->player_league_tier) != 0){
        $query->whereIn('league_tier', $this->player_league_tier);
      }
      if(count($this->hero_league_tier) != 0){
        $query->whereIn('hero_league_tier', $this->hero_league_tier);
      }

      if(count($this->role_league_tier) != 0){
        $query->whereIn('role_league_tier', $this->role_league_tier);
      }

      if(count($this->game_map) != 0){
        $query->whereIn('game_map', $this->game_map);
      }

      if(count($this->hero_level) != 0){
        $query->whereIn('hero_level', $this->hero_level);
      }

      $query->join('heroes', 'heroes.id', '=', 'global_hero_stats_bans.hero')
      ->select('heroes.name', DB::raw('SUM(bans) as bans'))
      ->groupBy('name');
      $data = $query->get();
      $data = json_decode(json_encode($data),true);

      $ban_data = array();
      $total_ban_games = 0;
      for($i = 0; $i < count($data); $i++){
        $ban_data[$data[$i]["name"]] = $data[$i]["bans"];
      }
      $change_data = array();
      if(count($this->timeframe) == 1 && count($this->game_type) == 1 && $this->game_type[0] != "br" && count($this->game_map) == 0 && count($this->player_league_tier) == 0 && count($this->hero_level) == 0){
        $change_data = $this->getChangeData();
      }
      for($i = 0; $i < count($return_data); $i++){
        if(!array_key_exists("wins", $return_data[$i])){
          $return_data[$i]["wins"] = 0;
        }

        if(!array_key_exists("losses", $return_data[$i])){
          $return_data[$i]["losses"] = 0;
        }
        //In this section where I have the same var as _influence, it is due to multiplying a value by 100 and rounding
        //We can remove this extra var if we move the modification of the final value to vue right before it gets displayed?

        if($return_data[$i]["wins"] == 0){
            $return_data[$i]["win_rate"] = 0;
        }else if($return_data[$i]["losses"] == 0){
          $return_data[$i]["win_rate"] = 100;
        }else{
          $return_data[$i]["win_rate"] = floatval(round(($return_data[$i]["wins"] / ($return_data[$i]["wins"] + $return_data[$i]["losses"])) * 100, 2));
          //$return_data[$i]["win_rate_influence"] = floatval($return_data[$i]["wins"] / ($return_data[$i]["wins"] + $return_data[$i]["losses"]));
        }


        if(!array_key_exists($return_data[$i]["name"]["hero_name"], $ban_data)){
          $return_data[$i]["bans"] = 0;
          $return_data[$i]["ban_rate"] = 0;
          //$return_data[$i]["ban_rate_influence"] = 0;
          $return_data[$i]["popularity"] = round(($return_data[$i]["games_played"] / ($total_games / 10)) * 100, 2);
        }else{
          $return_data[$i]["bans"] = $ban_data[$return_data[$i]["name"]["hero_name"]];
          $return_data[$i]["ban_rate"] = floatval(round(($return_data[$i]["bans"] / ($total_games / 10)) * 100, 2));
          //$return_data[$i]["ban_rate_influence"] = floatval($return_data[$i]["bans"] / ($total_games / 10));
          $return_data[$i]["popularity"] = round((($return_data[$i]["bans"] + $return_data[$i]["games_played"]) / ($total_games / 10)) * 100, 2);
        }

        $return_data[$i]["pick_rate"] = round(($return_data[$i]["games_played"] / ($total_games / 10)) * 100, 2);
        //$return_data[$i]["pick_rate_influence"] = floatval($return_data[$i]["games_played"] / ($total_games / 10));

        //$return_data[$i]["adjusted_pick_rate"] = floatval((100 * $return_data[$i]["pick_rate_influence"]) / (100 - (100 * $return_data[$i]["ban_rate_influence"])));
        //$return_data[$i]["influence"] = round(($return_data[$i]["win_rate_influence"] - .5) * ($return_data[$i]["adjusted_pick_rate"] * 10000));


        if(count($this->stat_type) != 0){
          if(array_key_exists($this->stat_type[0], $return_data[$i])){
            $return_data[$i][$this->stat_type[0]] /= $return_data[$i]["games_played"];
          }else{
            $return_data[$i][$this->stat_type[0]] = 0;
          }
        }

        /* fix later
        if(count($this->timeframe) == 1 && count($this->game_type) == 1 && $this->game_type[0] != "br" && count($this->game_map) == 0 && count($this->player_league_tier) == 0 && count($this->hero_level) == 0){
          $return_data[$i]["change"] = floatval(number_format($return_data[$i]["win_rate"] - $change_data[$return_data[$i]["name"]["hero_name"]], 2));
        }else{
          $return_data[$i]["change"] = 0;
        }
        */

      }
      for($i = 0; $i < count($return_data); $i++){
        $return_data[$i]["sortable"] = "true";
      }



      return $return_data;
    });

    if(count($this->role) != 0){
      $new_return_data = array();
      $new_data_counter = 0;
      for($i = 0; $i < count($return_data); $i++){
        if(Session::get("roles_by_hero_name")[$return_data[$i]["name"]["hero_name"]] == $this->role[0]){
          $new_return_data[$new_data_counter] = $return_data[$i];
          $new_data_counter++;
        }
      }
      $return_data = $new_return_data;
    }


    if(count($this->hero) != 0){
      $new_return_data = array();
      $new_data_counter = 0;
      for($i = 0; $i < count($return_data); $i++){

        for($j = 0; $j < count($this->hero); $j++){
          if($return_data[$i]["name"]["hero_name"] == $this->hero[$j]){
            $new_return_data[$new_data_counter] = $return_data[$i];
            $new_data_counter++;
          }
        }

      }
      $return_data = $new_return_data;
    }

    return $return_data;
  }
  private function getChangeData(){
    $timeframe = "";
    if($this->timeframe_type[0] == "major"){
      $sql = "SELECT DISTINCT(SUBSTRING_INDEX(game_version, '.', 2)) as game_version FROM heroesprofile.season_game_versions where game_version >= 2.43 order by game_version DESC";
    }else{
      $sql = "SELECT game_version FROM heroesprofile.season_game_versions where game_version >= 2.43 order by game_version DESC";
    }

    $patch_data = DB::connection('mysql')->select($sql);
    $patch_data = json_decode(json_encode($patch_data),true);


    $bool = "found";
    for($i = 0; $i < count($patch_data); $i++){
      if($bool == "true"){
        $timeframe = $patch_data[$i]["game_version"];
        break;
      }
      if($patch_data[$i]["game_version"] == $this->timeframe[0]){
        $bool = "true";
      }
    }

    $sql = "SELECT name, win_rate FROM heroesprofile_cache.global_hero_change inner join heroes on heroes.id = heroesprofile_cache.global_hero_change.hero WHERE game_version = " . "\"" . $timeframe . "\"" . " AND game_type = " . $this->game_type[0];
    $data = DB::connection('mysql')->select($sql);
    $data = json_decode(json_encode($data),true);

    $return_array = array();
    for($i = 0; $i < count($data); $i++){
      $return_array[$data[$i]["name"]] = $data[$i]["win_rate"];
    }
    return $return_array;
  }

  public function getHeroTalentsTableData(Request $request){
  }

  public function getHeroBuildsTableData(Request $request){
    $hero_array = Session::get("heroes_by_name");
    if(isset($request["timeframe_type"])){
      $this->timeframe_type[0] = $request["timeframe_type"];
    }else{
      $this->timeframe_type[0] = "minor";
    }

    if(isset($request["timeframe"])){
      $this->timeframe = explode(',', $request["timeframe"]);
    }else{
      $this->timeframe = Session::get("minor_patch_latest");
    }


    if(isset($request["game_type"]) && $request["game_type"] != ""){
      $this->game_type = explode(',', $request["game_type"]);
    }else{
      $this->game_type = array(Session::get("default_game_mode_id"));
    }

    if(isset($request["player_league_tier"]) && $request["player_league_tier"] != ""){
      $this->player_league_tier = explode(',', $request["player_league_tier"]);
    }

    if(isset($request["game_map"]) && $request["game_map"] != ""){
      $this->game_map =  explode(',', $request["game_map"]);
      for($i = 0; $i < count($this->game_map); $i++){
        $this->game_map[$i] = $maps[$this->game_map[$i]];
      }
    }

    if(isset($request["hero_level"]) && $request["hero_level"] != ""){
      $this->hero_level = explode(',', $request["hero_level"]);
    }

    if(isset($request["hero"]) && $request["hero"] != ""){
      $this->hero = array($hero_array[$request["hero"]]);
    }

    if(isset($request["build_type"]) && $request["build_type"] != ""){
      $this->build_type = $request["build_type"];
    }
    $this->build_type = "Popular";


    $page = "GlobalTalentBuilds";
    $cache =  $page .
              "|" . implode(",", $this->timeframe_type) .
              "|" . implode(",", $this->timeframe) .
              "|" . implode(",", $this->stat_type) .
              "|" . implode(",", $this->hero_level) .
              "|" . implode(",", $this->role) .
              "|" . implode(",", $this->hero) .
              "|" . implode(",", $this->game_type) .
              "|" . implode(",", $this->game_map) .
              "|"  . implode(",", $this->player_league_tier) .
              "|"  . implode(",", $this->hero_league_tier) .
              "|"  . implode(",", $this->role_league_tier);

    $return_data = Cache::remember($cache, 900, function () use ($request){
      $most_played_builds = $this->getTopFiveBuilds($request);
      for($i = 0; $i < count($most_played_builds); $i++){

        $most_played_builds[$i]["wins"] = 0;
        $most_played_builds[$i]["losses"] = 0;

        $most_played_builds[$i]["wins"] += $this->getWinLoss(1, "wins", $most_played_builds[$i], 10);
        $most_played_builds[$i]["wins"] += $this->getWinLoss(1, "wins", $most_played_builds[$i], 13);
        $most_played_builds[$i]["wins"] += $this->getWinLoss(1, "wins", $most_played_builds[$i], 16);
        $most_played_builds[$i]["wins"] += $this->getWinLoss(1, "wins", $most_played_builds[$i], 20);


        $most_played_builds[$i]["losses"] += $this->getWinLoss(0, "losses", $most_played_builds[$i], 10);
        $most_played_builds[$i]["losses"] += $this->getWinLoss(0, "losses", $most_played_builds[$i], 13);
        $most_played_builds[$i]["losses"] += $this->getWinLoss(0, "losses", $most_played_builds[$i], 16);
        $most_played_builds[$i]["losses"] += $this->getWinLoss(0, "losses", $most_played_builds[$i], 20);

      }

      for($i = 0; $i < count($most_played_builds); $i++){
        if($most_played_builds[$i]["wins"] == 0 && $most_played_builds[$i]["losses"] == 0){
          $most_played_builds[$i]["wins"] = 0;
          $most_played_builds[$i]["losses"] = 0;
          $most_played_builds[$i]["win_rate"] = 0;
        }else if($most_played_builds[$i]["wins"] != 0 && $most_played_builds[$i]["losses"] == 0){
          $most_played_builds[$i]["losses"] = 0;
          $most_played_builds[$i]["win_rate"] = 100;
        }else if($most_played_builds[$i]["wins"] != 0 && $most_played_builds[$i]["losses"] != 0){
          $most_played_builds[$i]["win_rate"] = ($most_played_builds[$i]["wins"] / ($most_played_builds[$i]["wins"] + $most_played_builds[$i]["losses"])) * 100;
        }
      }

      usort($most_played_builds, [$this, 'custom_win_rate_sort']);

      for($i = 0; $i < count($most_played_builds); $i++){
        foreach($this->talent_levels as $key=>$level){
          $most_played_builds[$i]['talents'][$key]['level'] = $level;
          $most_played_builds[$i]['talents'][$key]['id'] = $most_played_builds[$i][$level];
          $most_played_builds[$i]['talents'][$key]['name'] = Session::get('talent_data')[$most_played_builds[$i][$level]]['title'];
          $most_played_builds[$i]['talents'][$key]['icon'] = Session::get('talent_data')[$most_played_builds[$i][$level]]['icon'];
          $most_played_builds[$i]['talents'][$key]['description'] = Session::get('talent_data')[$most_played_builds[$i][$level]]['description'];
        }
      }
      return $most_played_builds;
    });
    return $return_data;
  }

  function custom_win_rate_sort( $a, $b ) {
    if($a["win_rate"] ==  $b["win_rate"] ){ return 0 ; }
    return ($a["win_rate"] > $b["win_rate"]) ? -1 : 1;
  }

  private function getTopFiveBuilds(Request $request){
    $maps = Session::get("maps_by_name");
    $hero_array = Session::get("heroes_by_name");

    $this->build_type = "Popular";    // For Testing

    $query = DB::table('heroesprofile.global_hero_talents');
    $query->join('heroesprofile.talent_combinations', 'heroesprofile.talent_combinations.talent_combination_id', '=', 'heroesprofile.global_hero_talents.talent_combination_id');




    if(count($this->timeframe) != 0){
      if($this->timeframe_type[0] == "major"){
        $patches_array = array();
        for($i = 0; $i < count($this->timeframe); $i++){
          if(count($patches_array) == 0){
            $patches_array = Session::get('major_to_minor_patch_mapping')[$this->timeframe[$i]];
          }else{
            array_merge($patches_array, Session::get('major_to_minor_patch_mapping')[$this->timeframe[$i]]);
          }
        }
        $query->whereIn('global_hero_talents.game_version', $patches_array);
      }else{
        $query->whereIn('global_hero_talents.game_version', $this->timeframe);
      }
    }
    if(count($this->game_type) != 0){
      $query->whereIn('global_hero_talents.game_type', $this->game_type);
    }

    //$query->where('global_hero_talents.hero', $hero_array[$this->hero]);
    $query->where('global_hero_talents.hero', $this->hero);

    if(count($this->player_league_tier) != 0){
      $query->whereIn('global_hero_talents.league_tier', $this->player_league_tier);
    }

    if(count($this->hero_league_tier) != 0){
      $query->whereIn('global_hero_talents.hero_league_tier', $this->hero_league_tier);
    }

    if(count($this->role_league_tier) != 0){
      $query->whereIn('global_hero_talents.role_league_tier', $this->role_league_tier);
    }

    if(count($this->game_map) != 0){
      $query->whereIn('global_hero_talents.game_map', $this->game_map);
    }

    if(count($this->hero_level) != 0){
      $query->whereIn('global_hero_talents.hero_level', $this->hero_level);
    }


    $query->where('talent_combinations.level_twenty', '<>', '0');
    $query->select('global_hero_talents.game_type', 'global_hero_talents.hero', 'talent_combinations.level_one','talent_combinations.level_four','talent_combinations.level_seven','talent_combinations.level_ten','talent_combinations.level_thirteen','talent_combinations.level_sixteen','talent_combinations.level_twenty', DB::raw('SUM(games_played) as games_played'));
    $query->groupBy('global_hero_talents.game_type', 'global_hero_talents.hero', 'talent_combinations.level_one','talent_combinations.level_four','talent_combinations.level_seven','talent_combinations.level_ten','talent_combinations.level_thirteen','talent_combinations.level_sixteen','talent_combinations.level_twenty');
    $query->orderBy('games_played', 'DESC');

    if($this->build_type == "Popular"){
      $query->limit(5);
    }else{
      $query->limit(100);
    }
    $build_data = $query->get();

    /*
    print_r($query->toSql());
    echo "<br>";
    echo "<br>";
    print_r($query->getBindings());
    */



    $build_data = json_decode(json_encode($build_data),true);
    $return_data = array();
    $counter = 0;

    for($i = 0; $i < count($build_data); $i++){
      $data = array();
      foreach($this->talent_levels as $level){
        $data[$level] = $build_data[$i][$level];
      }


      if($this->build_type == "Popular"){
        $return_data[$counter] = $data;
        $counter++;
      }else{

        if($counter != 0){
          $foundMatch = false;
          for($j = 0; $j < count($return_data); $j++){

            if($this->build_type == "HP"){
              if($data["level_one"] == $return_data[$j]["level_one"] && $data["level_four"] == $return_data[$j]["level_four"] && $data["level_seven"] == $return_data[$j]["level_seven"]){
                $foundMatch = true;
              }
            }else if($this->build_type == "1"){
              if($data["level_one"] == $return_data[$j]["level_one"]){
                $foundMatch = true;
              }
            }else if($this->build_type == "4"){
              if($data["level_four"] == $return_data[$j]["level_four"]){
                $foundMatch = true;
              }
            }else if($this->build_type == "7"){
              if($data["level_seven"] == $return_data[$j]["level_seven"]){
                $foundMatch = true;
              }
            }else if($this->build_type == "10"){
              if($data["level_ten"] == $return_data[$j]["level_ten"]){
                $foundMatch = true;
              }
            }else if($this->build_type == "13"){
              if($data["level_thirteen"] == $return_data[$j]["level_thirteen"]){
                $foundMatch = true;
              }
            }else if($this->build_type == "16"){
              if($data["level_sixteen"] == $return_data[$j]["level_sixteen"]){
                $foundMatch = true;
              }
            }else if($this->build_type == "20"){
              if($data["level_twenty"] == $return_data[$j]["level_twenty"]){
                $foundMatch = true;
              }
            }
          }

          if(!$foundMatch){
              $return_data[$counter] = $data;
              $counter++;
          }

        }else{
          $return_data[$counter] = $data;
          $counter++;
        }

        if($counter == 5){
          break;
        }

      }
    }

    return $return_data;



  }

  private function getWinLoss($value, $winLoss, $most_played_builds, $level){
    $maps = Session::get("maps_by_name");
    $hero_array = Session::get("heroes_by_name");


    $query = DB::table('heroesprofile.global_hero_talents');
    $query->join('heroesprofile.talent_combinations', 'heroesprofile.talent_combinations.talent_combination_id', '=', 'heroesprofile.global_hero_talents.talent_combination_id');
    if(count($this->timeframe) != 0){
      if($this->timeframe_type[0] == "major"){
        $patches_array = array();
        for($i = 0; $i < count($this->timeframe); $i++){
          if(count($patches_array) == 0){
            $patches_array = Session::get('major_to_minor_patch_mapping')[$this->timeframe[$i]];
          }else{
            array_merge($patches_array, Session::get('major_to_minor_patch_mapping')[$this->timeframe[$i]]);
          }
        }
        $query->whereIn('global_hero_talents.game_version', $patches_array);
      }else{
        $query->whereIn('global_hero_talents.game_version', $this->timeframe);
      }
    }
    if(count($this->game_type) != 0){
      $query->whereIn('global_hero_talents.game_type', $this->game_type);
    }
    $query->where('global_hero_talents.hero', $this->hero);

    if(count($this->player_league_tier) != 0){
      $query->whereIn('global_hero_talents.league_tier', $this->player_league_tier);
    }

    if(count($this->hero_league_tier) != 0){
      $query->whereIn('global_hero_talents.hero_league_tier', $this->hero_league_tier);
    }

    if(count($this->role_league_tier) != 0){
      $query->whereIn('global_hero_talents.role_league_tier', $this->role_league_tier);
    }

    if(count($this->game_map) != 0){
      $query->whereIn('global_hero_talents.game_map', $this->game_map);
    }

    if(count($this->hero_level) != 0){
      $query->whereIn('global_hero_talents.hero_level', $this->hero_level);
    }


    $query->where('global_hero_talents.win_loss', $value);
    $query->where('talent_combinations.level_one', $most_played_builds["level_one"]);
    $query->where('talent_combinations.level_four', $most_played_builds["level_four"]);
    $query->where('talent_combinations.level_seven', $most_played_builds["level_seven"]);
    $query->where('talent_combinations.level_ten', $most_played_builds["level_ten"]);

    if($level == 13){
      $query->where('talent_combinations.level_thirteen', $most_played_builds["level_thirteen"]);
    }else if($level == 16){
      $query->where('talent_combinations.level_thirteen', $most_played_builds["level_thirteen"]);
      $query->where('talent_combinations.level_sixteen', $most_played_builds["level_sixteen"]);
    }else if($level == 20){
      $query->where('talent_combinations.level_thirteen', $most_played_builds["level_thirteen"]);
      $query->where('talent_combinations.level_sixteen', $most_played_builds["level_sixteen"]);
      $query->where('talent_combinations.level_twenty', $most_played_builds["level_twenty"]);
    }

    $query->select(DB::raw('SUM(games_played) as games_played'));

    $build_data = $query->get();
    $build_data = json_decode(json_encode($build_data),true);
    return $build_data[0]["games_played"];
  }
}
