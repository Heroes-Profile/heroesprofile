<?php
namespace App\Functions;
use Illuminate\Support\Facades\DB;
use App\LeagueTier;
use App\SeasonDate;
use App\GameType;

use DateTime;
use Session;

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
  public function convertToFilter($array){
    $return_data = array();
    $counter = 0;
    foreach ($array as $key => $value){
      $data = array();
      $data["key"] = $key;
      $data["value"] = $value;
      $return_data[$counter] = $data;
      $counter++;
    }
    return $return_data;
  }

  public function getMaps($key_value){

    switch ($key_value) {
        case "map_id":
            $value = "name";
            break;
        case "name":
            $value = "map_id";
            break;
    }


    $maps = DB::table('heroesprofile.maps')->select('map_id', 'name')->orderBy('name', 'ASC')->get();
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
    $return_array = array();
    for($i = 0; $i < count($roles); $i++){
      $return_array[$roles[$i]["role"]] = $roles[$i]["role"];
    }

    return $return_array;
  }

  /*
  |--------------------------------------------------------------------------
  | getLeagueTiersByName
  |--------------------------------------------------------------------------
  |
  | This function maps league tier names to their id value
  |
  */

  public function getLeagueTiersByName(){
    $tiers = DB::table('heroesprofile.league_tiers')->select('name', 'tier_id')->where('name', '<>', 'all')->orderBy('tier_id', 'DESC')->get();
    $tiers = json_decode(json_encode($tiers), true);
    $returnData = array();

    foreach($tiers as $index => $tier){
      $returnData[ucfirst($tier['name'])] = $tier['tier_id'];
    }
    return $returnData;

}

/*
public function getLeagueTiers(){
  $tiers = DB::table('heroesprofile.league_tiers')->select('name', 'tier_id')->get();
  $tiers = json_decode(json_encode($tiers), true);
  $returnData = array();

  foreach($tiers as $index => $tier){
    $returnData[$tier['name']] = $tier['name'];
  }
  return $returnData;

}
*/


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
  | This function returns the league tiers as a collection
  |
  */

  public function getLeagueTiers(){
    return LeagueTier::where('name', '<>', 'all')->get();
  }


  /*
  |--------------------------------------------------------------------------
  | getGameTypesBy
  |--------------------------------------------------------------------------
  |
  | This function returns the game types by name or type_id
  |
  */

  public function getGameTypesBy($key_value){
    switch ($key_value) {
        case "name":
            $value = "type_id";
            break;
        case "type_id":
            $value = "name";
            break;
    }

    $game_types = GameType::all();
    $game_types = json_decode(json_encode($game_types),true);
    $return_data = array();
    for($i = 0; $i < count($game_types); $i++){
      $return_data[$game_types[$i][$key_value]] = $game_types[$i][$value];
    }

    return $return_data;
  }

  /*
  |--------------------------------------------------------------------------
  | getSeasonDates
  |--------------------------------------------------------------------------
  |
  | This function maps season id values to its data. (season, year, start and end dates)
  |
  */
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




  /*
  |--------------------------------------------------------------------------
  | getLeagueTierBreakdowns
  |--------------------------------------------------------------------------
  |
  | This function returns the league breakdowns for each league
  |
  */

  public function getLeagueTierBreakdowns(){
    $qm = $this->getLeaguesBreakDowns("1");
    $hl = $this->getLeaguesBreakDowns("2");
    $tl = $this->getLeaguesBreakDowns("3");
    $ud = $this->getLeaguesBreakDowns("4");
    $sl = $this->getLeaguesBreakDowns("5");

    $league_breakdowns = array(
        "1"  => $qm,
        "2" => $ud,
        "3" => $tl,
        "4" => $ud,
        "5" => $sl,
      );
    return $league_breakdowns;
  }

  /*
  |--------------------------------------------------------------------------
  | getLeaguesBreakDowns
  |--------------------------------------------------------------------------
  |
  | This function grabs league breakdown data for a specific game mode
  |
  */

  private function getLeaguesBreakDowns($league){
    $query = DB::table('heroesprofile.league_breakdowns');
    $query->where('type_role_hero', '10000');
    $query->where('game_type', $league);
    $league_data = $query->get();
    $league_data = json_decode(json_encode($league_data),true);
    $prevMin = 0;
    $returnData = array();
    for($i = 0; $i < count($league_data); $i++){
      $data = array();
      $data["min_mmr"] = $prevMin;
      $data["max_mmr"] = round($league_data[$i]["min_mmr"]);
      $prevMin = round($league_data[$i]["min_mmr"]);

      if($data["min_mmr"] == 0){
        $data["split"] = ($data["max_mmr"] - 1800) / 5;

      }else{
        $data["split"] = ($data["max_mmr"] - $data["min_mmr"]) / 5;
      }

      if($league_data[$i]["league_tier"] == "2"){
        $returnData["bronze"] = $data;
      }else if($league_data[$i]["league_tier"] == "3"){
        $returnData["silver"] = $data;
      }else if($league_data[$i]["league_tier"] == "4"){
        $returnData["gold"] = $data;
      }else if($league_data[$i]["league_tier"] == "5"){
        $returnData["platinum"] = $data;
      }else if($league_data[$i]["league_tier"] == "6"){
        $returnData["diamond"] = $data;
      }
    }

    $data["min_mmr"] = $prevMin;
    $data["max_mmr"] = "";
    $returnData["master"] = $data;
    return $returnData;

  }

  /*
  |--------------------------------------------------------------------------
  | getPlayerLeagueTier
  |--------------------------------------------------------------------------
  |
  | This function is used to return the league tier for a player.
  | It returns anything below master with a number denoting how low/high they are in that tier.
  | Example:  Bronze 5
  |
  */

  public function getPlayerLeagueTier($game_type, $mmr){
    $leagues = Session::get('leagues_breakdowns')[$game_type];
    //print_r(json_encode($leagues, true));
    if($mmr >= $leagues["master"]["min_mmr"]){
      return "Master";
    }else if($mmr < $leagues["master"]["min_mmr"] && $mmr >= $leagues["diamond"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["diamond"]["min_mmr"] + ($i * $leagues["diamond"]["split"])) && $mmr < ($leagues["diamond"]["min_mmr"]  + (($i + 1) * $leagues["diamond"]["split"]))){
          return "Diamond " . (5 - $i);
        }
      }
      return "Diamond";
    }else if($mmr < $leagues["diamond"]["min_mmr"] && $mmr >= $leagues["platinum"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["platinum"]["min_mmr"] + ($i * $leagues["platinum"]["split"])) && $mmr < ($leagues["platinum"]["min_mmr"]  + (($i + 1) * $leagues["platinum"]["split"]))){
          return "Platinum " . (5 - $i);
        }
      }
      return "Platinum";
    }else if($mmr < $leagues["platinum"]["min_mmr"] && $mmr >= $leagues["gold"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["gold"]["min_mmr"] + ($i * $leagues["gold"]["split"])) && $mmr < ($leagues["gold"]["min_mmr"]  + (($i + 1) * $leagues["gold"]["split"]))){
          return "Gold " . (5 - $i);
        }
      }
      return "Gold";
    }else if($mmr < $leagues["gold"]["min_mmr"] && $mmr >= $leagues["silver"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["silver"]["min_mmr"] + ($i * $leagues["silver"]["split"])) && $mmr < ($leagues["silver"]["min_mmr"]  + (($i + 1) * $leagues["silver"]["split"]))){
          return "Silver " . (5 - $i);
        }
      }
      return "Silver";
    }else{
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["bronze"]["min_mmr"] + ($i * $leagues["bronze"]["split"])) && $mmr < ($leagues["bronze"]["min_mmr"]  + (($i + 1) * $leagues["bronze"]["split"]))){
          return "Bronze " . (5 - $i);
        }
      }
      return "Bronze";
    }
  }

  /*
  |--------------------------------------------------------------------------
  | sortKeyValueArray
  |--------------------------------------------------------------------------
  |
  | This function is a switch case for determining which custom sorter to use
  |
  */

  public function sortKeyValueArray($array, $sort_type){

    switch ($sort_type) {
    case "game_date_desc":
        uasort($array, [$this, 'cmp_game_date_desc']);
        break;
    case "game_date_desc":
        uasort($array, [$this, 'cmp_game_date_asc']);
        break;
    case "mmr_parsed_sorted_desc":
        uasort($array, [$this, 'cmp_mmr_parsed_desc']);
        break;
  case "mmr_parsed_sorted_asc":
      uasort($array, [$this, 'cmp_mmr_parsed_asc']);
      break;
    case "games_played_desc":
        uasort($array, [$this, 'cmp_games_played_desc']);
        break;
    case "win_rate_desc":
        uasort($array, [$this, 'cmp_win_rate_desc']);
        break;
    case "sort_dates":
        uasort($array, [$this, 'cmp_sort_dates']);
        break;
    }

    return $array;
  }


  /*
  |--------------------------------------------------------------------------
  | cmp_game_date_desc
  |--------------------------------------------------------------------------
  |
  | Sorts on game_date Descending
  |
  */

  private function cmp_game_date_desc( $a, $b ) {
    $ad = new DateTime($a['game_date']);
    $bd = new DateTime($b['game_date']);

    if($ad ==  $bd){
      return 0 ;
    }
    return ($ad > $bd) ? -1 : 1;
  }

  /*
  |--------------------------------------------------------------------------
  | cmp_game_date_asc
  |--------------------------------------------------------------------------
  |
  | Sorts on game_date ascending
  |
  */

  private function cmp_game_date_asc( $a, $b ) {
    $ad = new DateTime($a['game_date']);
    $bd = new DateTime($b['game_date']);

    if($ad ==  $bd){
      return 0 ;
    }
    return ($ad < $bd) ? -1 : 1;
  }

  /*
  |--------------------------------------------------------------------------
  | cmp_mmr_parsed_desc
  |--------------------------------------------------------------------------
  |
  | Sorts on mmr_date_parsed Descending
  |
  */

  private function cmp_mmr_parsed_desc( $a, $b ) {
    $ad = new DateTime($a['mmr_date_parsed']);
    $bd = new DateTime($b['mmr_date_parsed']);

    if($ad ==  $bd){
      return 0 ;
    }
    return ($ad > $bd) ? -1 : 1;
  }

  /*
  |--------------------------------------------------------------------------
  | cmp_mmr_parsed_asc
  |--------------------------------------------------------------------------
  |
  | Sorts on mmr_date_parsed ascending
  |
  */

  private function cmp_mmr_parsed_asc( $a, $b ) {
    $ad = new DateTime($a['mmr_date_parsed']);
    $bd = new DateTime($b['mmr_date_parsed']);

    if($ad ==  $bd){
      return 0 ;
    }
    return ($ad < $bd) ? -1 : 1;
  }

  /*
  |--------------------------------------------------------------------------
  | cmp_games_played_desc
  |--------------------------------------------------------------------------
  |
  | Sorts on games_played Descending
  |
  */
  function cmp_games_played_desc( $a, $b ) {
    if(  $a["games_played"] ==  $b["games_played"] ){
      return 0 ;
    }
    return ($a["games_played"] > $b["games_played"]) ? -1 : 1;
  }

  /*
  |--------------------------------------------------------------------------
  | cmp_win_rate_desc
  |--------------------------------------------------------------------------
  |
  | Sorts on win_rate Descending
  |
  */
  function cmp_win_rate_desc( $a, $b ) {
    if(  $a["win_rate"] ==  $b["win_rate"] ){
      return 0 ;
    }
    return ($a["win_rate"] > $b["win_rate"]) ? -1 : 1;
  }


  /*
  |--------------------------------------------------------------------------
  | cmp_sort_dates
  |--------------------------------------------------------------------------
  |
  | sorts an array of dates
  |
  */
  function cmp_sort_dates( $a, $b ) {
    $t1 = strtotime($a['game_date']);
    $t2 = strtotime($b['game_date']);
    return $t1 - $t2;
  }
}

?>
