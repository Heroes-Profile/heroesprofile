<?php

if (!function_exists('calcluateCacheTime')) {
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
    function calcluateCacheTime($timeframe_type, $timeframe){
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
            return 30; //30 miniutes
          }
        }
      }
    }else{//Minor TimeFrames
      //Still need to do logic for this one
      return 30; //30 miniutes
    }
  }
}


if (!function_exists('getAllMinorPatches')) {
    /**
     * Returns an array that contains a list of minor patches
     * mapped to major patches
     *
     *
     * @return array major/minor patches
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
       $heroes = DB::table('heroesprofile.heroes')->select($key_value, $value)->get();
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
       return 86400;
     }
}


if (!function_exists('getTalentIDMap')) {
    /**
     * This function gets all of the heroes and their internal IDs
     *
     *
     * @return array array of regions
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
