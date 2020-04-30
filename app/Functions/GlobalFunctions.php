<?php

if (!function_exists('calculateCacheTime')) {
    /**
     * Returns the cache time in seconds
     *
     * @param array $timeframe_type
     * Major or Minor
     *
     * @param array $timeframe
     * The patches to filter on
     *
     * @return integer seconds to cache
     *
     * */
    function calculateCacheTime($timeframe_type, $timeframe){
    //Need to work on logic for this

    if($timeframe_type == "major"){

      //If the user chooses more than 1 major timeframe  e.g. (2.47, 2.48)
      if(count($timeframe) > 1){
        return 86400; //24 hours
      }else{
        //If the user chooses 1 timeframe, but it is not the latest major patch
        if($timeframe[0] != Session::get("major_patch")){
          return 86400; //24 hours
        }else{
          //If the major patches first minor patches release date was greater than 4 weeks ago
          if((Session::get("major_patch_earliest_date")[$timeframe[0]] < strtotime('-30 days'))){
            return 86400; //24 hours
          }else{
            return 60 * 60 * .5; //6 hours
          }
        }
      }
    }else{//Minor TimeFrames
      //Still need to do logic for this one
      return 60 * 60 * .5; //6 hours
      //return 1; //Testing
    }
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
      return max(array_keys(Session::get("season_dates")));
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

if (!function_exists('getCacheTimeGlobals')) {
    /**
     * This function returns the cache time value
     *
     *
     * @return array array of regions
     *
     * */
     function getCacheTimeGlobals(){
       return 86400; //24 hours
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
       $version_data = DB::table('season_game_versions')->select(DB::raw('DISTINCT(SUBSTRING_INDEX(game_version, ".", 2)) as major_game_version'), DB::raw('game_version as minor_game_version'))
       ->where('game_version', '>=', '2.44')
       ->orderBy('game_version', 'DESC')
       ->get();
       $version_data = json_decode(json_encode($version_data),true);

       $return_data = array();
       $counter = 0;
       $prev_major = "";
       for($i = 0; $i < count($version_data); $i++){
         if($prev_major != "" && $prev_major != $version_data[$i]["major_game_version"]){
           $counter = 0;
         }
         $return_data[$version_data[$i]["major_game_version"]][$counter] = $version_data[$i]["minor_game_version"];
         $counter++;
         $prev_major = $version_data[$i]["major_game_version"];
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
