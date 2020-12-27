<?php
use Carbon\Carbon;

if (!function_exists('checkAlertForHeader')) {
  /**
  * Returns true or false if there is an alert to display
  * */
  function checkAlertForHeader(){
    $valid = \App\Models\HeaderAlert::select('text')->where('valid', 1)->get();
    if(count($valid) > 0){
      return true;
    }else{
      return false;
    }
  }
}

if (!function_exists('getAlertHeader')) {
  /**
  * Gets header text value
  * */
  function getAlertHeader(){
    return \App\Models\HeaderAlert::select('text')->where('valid', 1)->get()[0]["text"];
  }
}

if (!function_exists('calculateCacheTime')) {
  /**
  * Returns the cache time in seconds
  *
  * @param string $timeframe_type  "Major" or "Minor"
  *
  * @param array $timeframe  The patches to filter on
  *
  * @return integer seconds to cache
  *
  * */
  function calculateCacheTime($timeframe_type, $timeframe){
    //Need to work on logic for this

    if(strtolower($timeframe_type) === "major"){

      //If the user chooses more than 1 major timeframe  e.g. (2.47, 2.48)
      if(count($timeframe) > 1){
        return 86400; //24 hours
      }else{
        //If the user chooses 1 timeframe, but it is not the latest major patch
        if($timeframe[0] != max(array_keys(getFilterVersions()))){
          return 86400; //24 hours
        }else{
          //If the major patches first minor patches release date was greater than 4 weeks ago
          $date = App\Models\SeasonGameVersions::max('date_added')->where('game_version', 'like', $timeframe[0] . '%');
          if(strtotime($date) < strtotime('-30 days')){
            return 86400; //24 hours
          }else if(strtotime($date) < strtotime('-15 days')){
            return 43200; //12 hours
          }else if(strtotime($date) < strtotime('-7 days')){
            return 43200; //6 hours
          }else{
            return 1800; //30 minutes
          }
        }
      }
    }else if(strtolower($timeframe_type) === "minor"){
      if(count($timeframe) > 1){
        return 86400; //24 hours
      }else{
        if($timeframe[0] != getMaxGameVersion()){
          return 86400; //24 hours
        }else{
          $date = (string) getMaxGameVersionForGlobalReleaseDate();
          if(strtotime($date) < strtotime('-30 days')){
            return 43200; //12 hours
          }else if(strtotime($date) < strtotime('-15 days')){
            return 21600; //6 hours
          }else if(strtotime($date) < strtotime('-7 days')){
            return 10800; //3 hours
          }else if(strtotime($date) < strtotime('-1 days')){
            return 300; //5 minutes
          }else{
            return 900; //15 minutes
          }
        }
      }
    }
    throw new \RuntimeException('Unknown timeframe type: ' . $timeframe_type);
  }
}

if (!function_exists('getMaxGameVersionForGlobalReleaseDate')) {
  /**
  * Returns the latest season ID
  *
  *
  * @return integer latest season
  *
  * */
  function getMaxGameVersionForGlobalReleaseDate(){
    return App\Models\SeasonGameVersions::max('date_added');
  }
}


if (!function_exists('getLatestSeason')) {
  /**
  * Returns the latest season ID
  *
  *
  * @return integer latest season
  *
  * */
  function getLatestSeason(){
    return App\Models\SeasonDate::max('id');
  }
}

if (!function_exists('getSeasonDates')) {
  /**
  * This function maps season id values to its data. (season, year, start and end dates)
  *
  *
  * @return array array of seasons
  *
  * */
  function getSeasonDates(){
    $season_data = App\Models\SeasonDate::all();
    $season_data = json_decode(json_encode($season_data),true);

    $return_data = array();
    for($i = 0; $i < count($season_data); $i++){
      $data = array();
      $data["season"] = $season_data[$i]["season"];
      $data["year"] = $season_data[$i]["year"];
      $data["start_date"] = date('Y-m-d H:i:s', strtotime($season_data[$i]["start_date"]));
      $data["end_date"] = date('Y-m-d H:i:s', strtotime($season_data[$i]["end_date"]));

      $return_data[$season_data[$i]["id"]] = $data;
    }
    return $return_data;
  }
}

if (!function_exists('getIntToRegion')) {
  /**
  * Maps the different regions to their integer equivalence.
  *
  *
  * @return array array of regions
  *
  * */
  function getIntToRegion(){
    $intToRegion = array(
      "1" => "NA",
      "2" => "EU",
      "3" => "KR",
      /*  "4" => "UNK",*/
      "5" => "CN"
    );
    return $intToRegion;
  }
}

if (!function_exists('getHeroesIDMap')) {
  /**
  * This function gets all of the heroes and their internal IDs
  *
  *
  * @return array array of regions
  *
  * */
  function getHeroesIDMap($key_value, $value){
    $heroes = DB::table('heroes')->select($key_value, $value)->get();
    $heroes = json_decode(json_encode($heroes),true);
    $return_data = array();
    for($i = 0; $i < count($heroes); $i++){
      $return_data[$heroes[$i][$key_value]] = $heroes[$i][$value];
    }
    return $return_data;
  }
}

if (!function_exists('getTalentIDMap')) {
  /**
  * This function maps talent_id and talent names
  *
  *
  * @return array array of talent data
  *
  * */
  function getTalentIDMap($hero, $key_value, $value){
    $talent_data = DB::table('heroes_data_talents')
    ->select($key_value, $value)
    ->where("hero_name", $hero)
    ->get();
    $talent_data = json_decode(json_encode($talent_data),true);
    $return_data = array();
    for($i = 0; $i < count($talent_data); $i++){
      $return_data[$talent_data[$i][$key_value]] = $talent_data[$i][$value];
    }
    return $return_data;
  }
}

if (!function_exists('getTalentData')) {
  /**
  * This function gets the talent data for a specific hero
  *
  *
  * @return array array of talent_id mapped to its data
  *
  * */
  function getTalentData($hero){
    $talent_data = DB::table('heroes_data_talents')->select('talent_id', 'title', 'description', 'hotkey', 'icon', 'short_name')
    ->where('hero_name', $hero)
    ->where('title', '<>', '')
    ->get();
    $talent_data = json_decode(json_encode($talent_data),true);

    $return_data = array();

    for($i = 0; $i < count($talent_data); $i++){
      $data = array();
      $data["talent_id"] = $talent_data[$i]["talent_id"];
      $data["short_name"] = $talent_data[$i]["short_name"];
      $data["title"] = $talent_data[$i]["title"];
      $data["description"] = $talent_data[$i]["description"];
      $data["icon"] = $talent_data[$i]["icon"];
      $return_data[$talent_data[$i]["talent_id"]] = $data;
    }
    return $return_data;
  }
}

if (!function_exists('getAllMinorPatches')) {
  /**
  * Returns an array that contains a list of minor patches
  *
  *
  * @return array minor patches
  *
  * */
  function getAllMinorPatches(){
    $minor_patches = DB::table('season_game_versions')->select("game_version")
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
}

if (!function_exists('getFilterVersions')) {
  /**
  * This function gets all of the game versions and maps them to major versions
  *
  *
  * @return array array of minor/major versions
  *
  * */
  function getFilterVersions(){
    $version_data = DB::table('season_game_versions')->select('game_version')
    ->where('game_version', '>=', '2.44')
    ->orderBy('game_version', 'DESC')
    ->get();

    $return_data = array();

    $counter = 0;
    $prev_major = "";
    for($i = 0; $i < count($version_data); $i++){
      $major = implode(".", array_slice(explode(".", $version_data[$i]->game_version), 0, 2));

      if($prev_major != "" && $prev_major != $major){
        $counter = 0;
      }
      $return_data[$major][$counter] = $version_data[$i]->game_version;
      $counter++;
      $prev_major = $major;
    }

    return $return_data;
  }
}

if (!function_exists('getFilterMaps')) {
  /**
  * This function gets all of the maps and groups them
  *
  *
  * @return array array of maps
  *
  * */
  function getFilterMaps(){
    $map_data = \App\Models\Map::where('playable', '1')->orderBy('type', 'DESC')->orderBy('name', 'ASC')->get();
    $return_data = array();
    $ranked_counter = 0;
    $extra_maps_counter = 0;
    $brawl_counter = 0;
    for($i = 0; $i < count($map_data); $i++){
      if($map_data[$i]->ranked_rotation == 1){
        $return_data["Ranked-Rotation"][$ranked_counter]["name"] = $map_data[$i]->name;
        $return_data["Ranked-Rotation"][$ranked_counter]["map_id"] = $map_data[$i]->map_id;
        $ranked_counter++;
      }else{
        if($map_data[$i]->type != "brawl"){
          $return_data["Extra-Maps"][$extra_maps_counter]["name"]  = $map_data[$i]->name;
          $return_data["Extra-Maps"][$extra_maps_counter]["map_id"]  = $map_data[$i]->map_id;
          $extra_maps_counter++;
        }
      }

      if($map_data[$i]->type == "brawl"){
        $return_data["Brawl"][$brawl_counter]["name"] = $map_data[$i]->name;
        $return_data["Brawl"][$brawl_counter]["map_id"] = $map_data[$i]->map_id;
        $brawl_counter++;
      }

    }
    return $return_data;
  }
}

if (!function_exists('getFilterMapsRanked')) {
  /**
  * This function gets all of the ranked maps and groups them
  *
  *
  * @return array array of maps
  *
  * */
  function getFilterMapsRanked(){
    $map_data = \App\Models\Map::where('playable', '1')->where('type', 'standard')->where('ranked_rotation', 1)->orderBy('type', 'DESC')->orderBy('name', 'ASC')->get();
    $return_data = array();
    $ranked_counter = 0;
    $extra_maps_counter = 0;
    $brawl_counter = 0;
    for($i = 0; $i < count($map_data); $i++){
      if($map_data[$i]->ranked_rotation == 1){
        $return_data["Ranked-Rotation"][$ranked_counter]["name"] = $map_data[$i]->name;
        $return_data["Ranked-Rotation"][$ranked_counter]["map_id"] = $map_data[$i]->map_id;
        $ranked_counter++;
      }else{
        if($map_data[$i]->type != "brawl"){
          $return_data["Extra-Maps"][$extra_maps_counter]["name"]  = $map_data[$i]->name;
          $return_data["Extra-Maps"][$extra_maps_counter]["map_id"]  = $map_data[$i]->map_id;
          $extra_maps_counter++;
        }
      }
    }
    return $return_data;
  }
}

if (!function_exists('getScoreStatsByGrouping')) {
  /**
  * This function gets all of the stats and groups them
  *
  *
  * @return array array of grouping and stats
  *
  * */
  function getScoreStatsByGrouping(){
    $return_data["Combat"][0] = "Kills";
    $return_data["Combat"][1] = "Assists";
    $return_data["Combat"][2] = "Takedowns";
    $return_data["Combat"][3] = "Deaths";

    $return_data["Player"][0] = "Regeneration Globes";
    $return_data["Player"][1] = "Hero Damage";
    $return_data["Player"][2] = "Physical Damage Done";
    $return_data["Player"][3] = "Spell Damage Done";
    $return_data["Player"][4] = "Damage Taken";
    $return_data["Player"][5] = "Time Spent Dead";
    $return_data["Player"][6] = "Enemy Silence Duration";
    $return_data["Player"][7] = "Enemy Rooted Duration";
    $return_data["Player"][8] = "Enemy Stunned Duration";
    $return_data["Player"][9] = "Escapes";
    $return_data["Player"][10] = "Vengeances";
    $return_data["Player"][11] = "Outnumbered Deaths";


    $return_data["Siege"][0] = "Siege Damage";
    $return_data["Siege"][1] = "Structure Damage";
    $return_data["Siege"][2] = "Minion Damage";
    $return_data["Siege"][3] = "Lane Mercenary Damage";
    $return_data["Siege"][4] = "Summon Damage";


    $return_data["Macro"][0] = "Experience Contribution";
    $return_data["Macro"][1] = "Mercenary Camp Captures";
    $return_data["Macro"][2] = "Watch Tower Captures";
    $return_data["Macro"][3] = "Team Experience";

    $return_data["Teamfight"][0] = "Teamfight Damage Taken";
    $return_data["Teamfight"][1] = "Teamfight Hero Damage";
    $return_data["Teamfight"][2] = "Teamfight Escapes";
    $return_data["Teamfight"][3] = "Teamfight Healing";


    $return_data["Defense/Healing"][0] = "Healing";
    $return_data["Defense/Healing"][1] = "Self Healing";
    $return_data["Defense/Healing"][2] = "Clutch Heals";
    $return_data["Defense/Healing"][3] = "Ally Protection";
    $return_data["Defense/Healing"][4] = "Crowd Control Enemies";

    return $return_data;
  }
}

if (!function_exists('getMMRTypeIDs')) {
  /*
  |--------------------------------------------------------------------------
  | getMMRTypeIDs
  |--------------------------------------------------------------------------
  |
  | Returns a mapping of MMR Type to MMR Type ID
  |
  */
  function getMMRTypeIDs(){
    $mmr_type_id_data = \App\Models\MMRTypeID::all();
    $mmr_type_id_data = json_decode(json_encode($mmr_type_id_data),true);

    $return_data = array();
    for($i = 0; $i < count($mmr_type_id_data); $i++){
      $return_data[$mmr_type_id_data[$i]["name"]] = $mmr_type_id_data[$i]["mmr_type_id"];
    }


    return $return_data;
  }
}

if (!function_exists('getLeagueTierBreakdown')) {
  /*
  |--------------------------------------------------------------------------
  | getLeagueTierBreakdowns
  |--------------------------------------------------------------------------
  |
  | This function returns the league breakdowns for each league
  |
  */
  function getLeagueTierBreakdown($game_type, $mmr_id){
    $query = DB::table('league_breakdowns');
    $query->where('type_role_hero', $mmr_id);
    $query->where('game_type', $game_type);
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
}

if (!function_exists('getRankSplit')) {
  /*
  |--------------------------------------------------------------------------
  | getLeagueTierBreakdowns
  |--------------------------------------------------------------------------
  |
  | This function returns the league breakdowns for each league
  |
  */
  function getRankSplit($mmr, $leagues){
    $rank_name = "";
    if($mmr >= $leagues["master"]["min_mmr"]){
      $rank_name = "Master";
    }else if($mmr < $leagues["master"]["min_mmr"] && $mmr >= $leagues["diamond"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["diamond"]["min_mmr"] + ($i * $leagues["diamond"]["split"])) && $mmr < ($leagues["diamond"]["min_mmr"]  + (($i + 1) * $leagues["diamond"]["split"]))){
          $rank_name = "Diamond " . (5 - $i);
          break;
        }else{
          $rank_name = "Diamond";
        }
      }
    }else if($mmr < $leagues["diamond"]["min_mmr"] && $mmr >= $leagues["platinum"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["platinum"]["min_mmr"] + ($i * $leagues["platinum"]["split"])) && $mmr < ($leagues["platinum"]["min_mmr"]  + (($i + 1) * $leagues["platinum"]["split"]))){
          $rank_name = "Platinum " . (5 - $i);
          break;
        }else{
          $rank_name = "Platinum";
        }
      }
    }else if($mmr < $leagues["platinum"]["min_mmr"] && $mmr >= $leagues["gold"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["gold"]["min_mmr"] + ($i * $leagues["gold"]["split"])) && $mmr < ($leagues["gold"]["min_mmr"]  + (($i + 1) * $leagues["gold"]["split"]))){
          $rank_name = "Gold " . (5 - $i);
          break;
        }else{
          $rank_name = "Gold";
        }
      }
    }else if($mmr < $leagues["gold"]["min_mmr"] && $mmr >= $leagues["silver"]["min_mmr"]){
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["silver"]["min_mmr"] + ($i * $leagues["silver"]["split"])) && $mmr < ($leagues["silver"]["min_mmr"]  + (($i + 1) * $leagues["silver"]["split"]))){
          $rank_name = "Silver " . (5 - $i);
          break;
        }else{
          $rank_name = "Silver";
        }
      }
    }else{
      for($i = 0; $i < 5; $i++){
        if($mmr >= ($leagues["bronze"]["min_mmr"] + ($i * $leagues["bronze"]["split"])) && $mmr < ($leagues["bronze"]["min_mmr"]  + (($i + 1) * $leagues["bronze"]["split"]))){
          $rank_name = "Bronze " . (5 - $i);
          break;
        }else{
          $rank_name = "Bronze";
        }
      }
    }




    return $rank_name;
  }
}

if (!function_exists('getMaxReplayID')) {
  /**
  * Returns the max replayID in the DB
  *
  *
  * @return integer max replayID
  *
  * */
  function getMaxReplayID(){
    return App\Models\Replay::max('replayID');
  }
}

if (!function_exists('getMaxGameVersion')) {
  /**
  * Returns the max game version in the DB
  *
  *
  * @return integer max game version
  *
  * */
  function getMaxGameVersion(){
    return App\Models\SeasonGameVersions::max('game_version');
  }
}

if (!function_exists('getMaxGameDate')) {
  /**
  * Returns the max game date in the DB
  *
  *
  * @return integer max game date
  *
  * */
  function getMaxGameDate(){
    return App\Models\Replay::where('game_date', '<=', Carbon::now())->max('game_date');
  }
}

if (!function_exists('getTalentMetaData')) {
  /**
  * Returns metadata for talents
  *
  *
  * @return array
  *
  * */
  function getTalentMetaData(){
    $talent_data = \App\Models\HeroesDataTalent::select('talent_id', 'talent_name', 'title', 'description', 'hotkey', 'icon')->get();
    $returnData = array();
    for($i = 0; $i < count($talent_data); $i++){
      $data = array();
      $data["talent_name"] = $talent_data[$i]["talent_name"];
      $data["title"] = $talent_data[$i]["title"];
      $data["description"] = $talent_data[$i]["description"];
      $data["hotkey"] = $talent_data[$i]["hotkey"];
      $data["icon"] = $talent_data[$i]["icon"];
      $returnData[$talent_data[$i]["talent_id"]] = $data;
    }
    return $returnData;
  }
}

if (!function_exists('getHeroRoles')) {
  /**
  * Returns the roles for each hero
  *
  *
  * @return array  roles for each hero
  *
  * */
  function getHeroRoles(){
    $heroes = \App\Models\Hero::select('id', 'name', 'new_role')->get();
    $returnData = array();
    for($i = 0; $i < count($heroes); $i++){
      $returnData[$heroes[$i]["id"]] = $heroes[$i]["new_role"];
    }
    return $returnData;
  }
}

if (!function_exists('getBlizzID')) {
  /**
  * Returns blizzID with given battletag and region
  *
  * */
  function getBlizzID($battletag, $region){
    return \App\Models\Battletag::select('blizz_id')->where('battletag', $battletag)->where('region', $region)->max('blizz_id');
  }
}
