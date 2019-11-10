<?php
namespace App\Functions;
use Illuminate\Support\Facades\DB;
use App\LeagueTier;
use App\SeasonDate;

use DateTime;

class GlobalFunctions
{
  public static function instance()
  {
      return new GlobalFunctions();
  }

  /*
  |--------------------------------------------------------------------------
  | getMaps
  |--------------------------------------------------------------------------
  |
  | This function gets all of the maps and their internal IDs
  |
  */

  public function getMaps($key_value){

    switch ($key_value) {
        case "map_id":
            $value = "name";
            break;
        case "name":
            $value = "map_id";
            break;
    }


    $maps = DB::table('heroesprofile.maps')->select('map_id', 'name')->get();
    $maps = json_decode(json_encode($maps),true);

    $return_data = array();

    for($i = 0; $i < count($maps); $i++){
      $return_data[$maps[$i][$key_value]] = $maps[$i][$value];
    }
    return $return_data;
  }

  /*
  |--------------------------------------------------------------------------
  | getHeroes
  |--------------------------------------------------------------------------
  |
  | This function gets all of the heroes and their internal IDs
  |
  */

  public function getHeroesIDMap($key_value){

    switch ($key_value) {
        case "id":
            $value = "name";
            break;
        case "name":
            $value = "id";
            break;
    }


    $heroes = DB::table('heroesprofile.heroes')->select('id', 'name')->get();
    $heroes = json_decode(json_encode($heroes),true);

    $return_data = array();

    for($i = 0; $i < count($heroes); $i++){
      $return_data[$heroes[$i][$key_value]] = $heroes[$i][$value];
    }
    return $return_data;
  }


  /*
  |--------------------------------------------------------------------------
  | getHeroesNameToShort
  |--------------------------------------------------------------------------
  |
  | This function gets all of the heroes and their internal IDs
  |
  */

  public function getHeroesNameToShort(){
    $heroes = DB::table('heroesprofile.heroes')->select('name', 'short_name')->get();
    $heroes = json_decode(json_encode($heroes),true);

    $return_data = array();

    for($i = 0; $i < count($heroes); $i++){
      $return_data[$heroes[$i]['name']] = $heroes[$i]['short_name'];
    }
    return $return_data;
  }

  /*
  |--------------------------------------------------------------------------
  | getRoles
  |--------------------------------------------------------------------------
  |
  | This function gets all of the heroes roles and their hero name
  |
  */

  public function getRoles($key_value){

    switch ($key_value) {
        case "name":
            $value = "new_role";
            break;
        case "new_role":
            $value = "name";
            break;
    }


    $roles = DB::table('heroesprofile.heroes')->select('name', 'new_role')->get();
    $roles = json_decode(json_encode($roles),true);

    $return_data = array();

    for($i = 0; $i < count($roles); $i++){
      $return_data[$roles[$i][$key_value]] = $roles[$i][$value];
    }
    return $return_data;
  }

  /*
  |--------------------------------------------------------------------------
  | getLatestMajorPatch
  |--------------------------------------------------------------------------
  |
  | This function gets the latest major patch
  |
  */

  public function getLatestMajorPatch(){
    $major_patch = DB::table('heroesprofile.season_game_versions')->select(DB::raw("DISTINCT(SUBSTRING_INDEX(game_version, '.', 2)) as game_version"))
    ->orderBy('game_version', 'desc')
    ->limit(1)
    ->get();
    $major_patch = json_decode(json_encode($major_patch),true);
    return $major_patch[0]["game_version"];
  }

  /*
  |--------------------------------------------------------------------------
  | getAllMajorPatches
  |--------------------------------------------------------------------------
  |
  | This function returns all of the major patches after 2.43
  |
  */

  public function getAllMajorPatches(){
    $major_patches = DB::table('heroesprofile.season_game_versions')->select(DB::raw("SUBSTRING_INDEX(game_version, '.', 2) as game_version"))
    ->where('game_version', '>=', '2.44')
    ->orderBy('game_version', 'desc')
    ->get();
    $major_patches = json_decode(json_encode($major_patches),true);

    $return_data = array();
    for($i = 0; $i < count($major_patches); $i++){
      $return_data[$major_patches[$i]["game_version"]] = $major_patches[$i]["game_version"];
    }

    //print_r(json_encode($major_patches, true));
    return $return_data;
  }

  /*
  |--------------------------------------------------------------------------
  | getAllMinorPatches
  |--------------------------------------------------------------------------
  |
  | This function returns all of the minor patches after 2.43
  |
  */

  public function getAllMinorPatches(){
    $minor_patches = DB::table('heroesprofile.season_game_versions')->select(DB::raw("game_version"))
    ->where('game_version', '>=', '2.44')
    ->orderBy('game_version', 'desc')
    ->get();
    $minor_patches = json_decode(json_encode($minor_patches),true);

    $return_data = array();
    for($i = 0; $i < count($minor_patches); $i++){
      $return_data[$minor_patches[$i]["game_version"]] = $minor_patches[$i]["game_version"];
    }
    return $return_data;
  }

  /*
  |--------------------------------------------------------------------------
  | getMajorMinorPatchMapping
  |--------------------------------------------------------------------------
  |
  | Gets all minor patches for a give major patch
  |
  */

  public function getMajorMinorPatchMapping(){
    $patch_data = DB::table('heroesprofile.season_game_versions')->select(DB::raw("SUBSTRING_INDEX(game_version, '.', 2) as major, game_version as minor"))
    ->where('game_version', '>=', '2.44')
    ->orderBy('major', 'desc')
    ->get();

    $patch_data = json_decode(json_encode($patch_data),true);
    //print_r(json_encode($patch_data, true));

    $return_data = array();
    $counter = 0;
    for($i = 0; $i < count($patch_data); $i++){
      if($i != 0){
        if($patch_data[$i]["major"] != $patch_data[$i - 1]["major"]){
          $counter = 0;
        }
      }
      $return_data[$patch_data[$i]["major"]][$counter] = $patch_data[$i]["minor"];

      $counter++;
    }
    return $return_data;
  }


  /*
  |--------------------------------------------------------------------------
  | getAllStatColumns
  |--------------------------------------------------------------------------
  |
  | This function returns the stat columns and their associative names
  |
  */

  public function getAllStatColumns(){
    return array("kills" => "Kills",
      "assists" => "Assists",
      "takedowns" => "Takedowns",
      "deaths" => "Deaths",
      "regen_globes" => "Regeneration Globes",
      "hero_damage" => "Hero Damage",
      "physical_damage" => "Physical Damage Done",
      "spell_damage" => "Spell Damage Done",
      "damage_taken" => "Damage Taken",
      "time_spent_dead" => "Time Spent Dead",
      "silencing_enemies" => "Enemy Silence Duration",
      "rooting_enemies" => "Enemy Rooted Duration",
      "stunning_enemies" => "Enemy Stunned Duration",
      "escapes" => "Escapes",
      "vengeance" => "Vengeances",
      "outnumbered_deaths" => "Outnumbered Deaths",
      "siege_damage" => "Siege Damage",
      "structure_damage" => "Structure Damage",
      "minion_damage" => "Minion Damage",
      "creep_damage" => "Creep Damage",
      "summon_damage" => "Summon Damage",
      "experience_contribution" => "Experience Contribution",
      "merc_camp_captures" => "Merc. Camp Captures",
      "watch_tower_captures" => "Watch Tower Captures",
      "meta_experience" => "Team Exp.",
      "teamfight_damage_taken" => "Teamfight Damage Taken",
      "teamfight_hero_damage" => "Teamfight Hero Damage",
      "teamfight_escapes" => "Teamfight Escapes",
      "teamfight_healing" => "Teamfight Healing",
      "healing" => "Healing",
      "self_healing" => "Self Healing",
      "clutch_heals" => "Clutch Heals",
      "protection_allies" => "Ally Protection",
      "time_cc_enemy_heroes" => "Crowd Control Enemies"
      );
  }

  /*
  |--------------------------------------------------------------------------
  | getHerolevels
  |--------------------------------------------------------------------------
  |
  | This function returns the hero level values
  |
  */

  public function getHerolevels(){
    return array(
      "1-5" => "1-5",
      "5-10" => "5-10",
      "10-15" => "10-15",
      "15-20" => "15-20",
      "20-999" => "20-999"
    );
  }

  /*
  |--------------------------------------------------------------------------
  | getRoleNames
  |--------------------------------------------------------------------------
  |
  | This function returns the role names
  |
  */

  public function getRoleNames(){
    $roles = DB::table('heroesprofile.heroes')->select(DB::raw("DISTINCT(new_role) as role"))
    ->orderBy('role', 'asc')
    ->get();
    $roles = json_decode(json_encode($roles),true);

    return $roles;
  }


  /*
  |--------------------------------------------------------------------------
  | talentData
  |--------------------------------------------------------------------------
  |
  | This function returns the role names
  |
  */

  public function getTalentData(){
    $talent_data = DB::table('heroesprofile.heroes_data_talents')->select('talent_id', 'hero_name', 'short_name', 'title', 'talent_name', 'description', 'level', 'icon')
    ->orderBy('hero_name', 'asc')
    ->orderBy('level', 'asc')
    ->orderBy('sort', 'asc')
    ->where('title', '<>', '')
    ->get();
    $talent_data = json_decode(json_encode($talent_data),true);

    $return_data = array();

    for($i = 0; $i < count($talent_data); $i++){
      $data = array();
      $data["hero"] = $talent_data[$i]["hero_name"];
      $data["short_name"] = $talent_data[$i]["short_name"];
      $data["title"] = $talent_data[$i]["title"];
      $data["talent_name"] = $talent_data[$i]["talent_name"];
      $data["description"] = $talent_data[$i]["description"];
      $data["level"] = $talent_data[$i]["level"];
      $data["icon"] = $talent_data[$i]["icon"];
      $return_data[$talent_data[$i]["talent_id"]] = $data;
    }
    return $return_data;
  }



  /*
  |--------------------------------------------------------------------------
  | getLeagueTiers
  |--------------------------------------------------------------------------
  |
  | This function returns the league tiers
  |
  */

  public function getLeagueTiers(){
    return LeagueTier::where('name', '<>', 'all')->get();
  }

  public function getSeasonDates(){
    $season_data = SeasonDate::all();
    $season_data = json_decode(json_encode($season_data),true);

    $return_data = array();
    for($i = 0; $i < count($season_data); $i++){
      $data = array();
      $data["season"] = $season_data[$i]["season"];
      $data["year"] = $season_data[$i]["year"];
      $data["start_date"] = $season_data[$i]["start_date"];
      $data["end_date"] = $season_data[$i]["end_date"];

      $return_data[$season_data[$i]["id"]] = $data;
    }
    return $return_data;
  }




  public function sortKeyValueArray($array, $sort_type){

    switch ($sort_type) {
    case "game_date_desc":
        uasort($array, [$this, 'cmp_game_date_desc']);
        break;
    case "mmr_parsed_sorted_desc":
        uasort($array, [$this, 'cmp_mmr_parsed_desc']);
        break;
    case "games_played_desc"
    uasort($array, [$this, 'cmp_games_played_desc']);
        break;
    }

    return $array;
  }

  private function cmp_game_date_desc( $a, $b ) {
    $ad = new DateTime($a['game_date']);
    $bd = new DateTime($b['game_date']);

    if($ad ==  $bd){
      return 0 ;
    }
    return ($ad > $bd) ? -1 : 1;
  }

  private function cmp_mmr_parsed_desc( $a, $b ) {
    $ad = new DateTime($a['mmr_date_parsed']);
    $bd = new DateTime($b['mmr_date_parsed']);

    if($ad ==  $bd){
      return 0 ;
    }
    return ($ad > $bd) ? -1 : 1;
  }

  function cmp_games_played_desc( $a, $b ) {
  if(  $a["games_played"] ==  $b["games_played"] ){
    return 0 ;
  }
  return ($a["games_played"] > $b["games_played"]) ? -1 : 1;
}


}

?>
