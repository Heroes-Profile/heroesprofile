<?php

namespace App\Http\Middleware;

use Closure;
use Cache;
use Session;
class GlobalsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $seconds = 86400; //24 hours

      if (!$request->session()->has('heroes_by_id')) {
        session(['heroes_by_id' => Cache::remember('heroes_by_id', $seconds, function () {
            return $this->setHeroesByCache("id");
        })]);
      }

      if (!$request->session()->has('heroes_by_name')) {
        session(['heroes_by_name' => Cache::remember('heroes_by_name', $seconds, function () {
            return $this->setHeroesByCache("name");
        })]);
      }

      if (!$request->session()->has('heroes_name_to_short')) {
        session(['heroes_name_to_short' => Cache::remember('heroes_name_to_short', $seconds, function () {
            return $this->setHeroesNameToShortCache();
        })]);
      }

      if (!$request->session()->has('maps_by_id')) {
        session(['maps_by_id' => Cache::remember('maps_by_id', $seconds, function () {
            return $this->setMapsByCache("map_id");
        })]);
      }

      if (!$request->session()->has('maps_by_name')) {
        session(['maps_by_name' => Cache::remember('maps_by_name', $seconds, function () {
            return $this->setMapsByCache("name");
        })]);
      }


      if (!$request->session()->has('roles_by_hero_name')) {
        session(['roles_by_hero_name' => Cache::remember('roles_by_hero_name', $seconds, function () {
            return $this->setRolesByCache("name");
        })]);
      }


      if (!$request->session()->has('roles_by_role_name')) {
        session(['roles_by_role_name' => Cache::remember('roles_by_role_name', $seconds, function () {
            return $this->setRolesByCache("new_role");
        })]);
      }

      if (!$request->session()->has('roles_by_id')) {
        session(['roles_by_id' => Cache::remember('roles_by_id', $seconds, function () {
            return $this->setRolesByCache("id");
        })]);
      }

      if (!$request->session()->has('default_game_mode_id')) {
        session(['default_game_mode_id' => Cache::remember('default_game_mode_id', $seconds, function () {
            return 5;
        })]);
      }

      if (!$request->session()->has('default_game_mode_name')) {
        session(['default_game_mode_name' => Cache::remember('default_game_mode_name', $seconds, function () {
            return "Storm League";
        })]);
      }

      if (!$request->session()->has('major_patch')) {
        session(['major_patch' => Cache::remember('major_patch', $seconds, function () {
            return $this->setMajorPatchCache();
        })]);
      }

      if (!$request->session()->has('minor_patch_two_latest')) {
        session(['minor_patch_two_latest' => Cache::remember('minor_patch_two_latest', $seconds, function () {
            return $this->setMinorPatchTwoLatest();
        })]);
      }

      if (!$request->session()->has('all_major_patch')) {
        session(['all_major_patch' => Cache::remember('all_major_patch', $seconds, function () {
            return $this->setAllMajorPatchCache();
        })]);
      }

      if (!$request->session()->has('all_minor_patch')) {
        session(['all_minor_patch' => Cache::remember('all_minor_patch', $seconds, function () {
            return $this->setAllMinorPatchCache();
        })]);
      }

      if (!$request->session()->has('major_to_minor_patch_mapping')) {
        session(['major_to_minor_patch_mapping' => Cache::remember('major_to_minor_patch_mapping', $seconds, function () {
            return $this->setMajorMinorPatchMappingCache();
        })]);
      }

      if (!$request->session()->has('stat_columns')) {
        session(['stat_columns' => Cache::remember('stat_columns', $seconds, function () {
            return $this->setStatColumnsCache();
        })]);
      }

      if (!$request->session()->has('hero_levels')) {
        session(['hero_levels' => Cache::remember('hero_levels', $seconds, function () {
            return $this->setHerolevelCache();
        })]);
      }

      if (!$request->session()->has('role_names')) {
        session(['role_names' => Cache::remember('role_names', $seconds, function () {
            return $this->setRoleNamesCache();
        })]);
      }

      if (!$request->session()->has('talent_data')) {
        session(['talent_data' => Cache::remember('talent_data', $seconds, function () {
            return $this->setTalentDataCache();
        })]);
      }

      if (!$request->session()->has('season_dates')) {
        session(['season_dates' => Cache::remember('season_dates', $seconds, function () {
            return $this->setSeasonDatesCache();
        })]);
      }

      if (!$request->session()->has('leagues_breakdowns')) {
        session(['leagues_breakdowns' => Cache::remember('leagues_breakdowns', $seconds, function () {
            return $this->setLeagueBreakdownsCache();
        })]);
      }

      if (!$request->session()->has('game_types_by_name')) {
        session(['game_types_by_name' => Cache::remember('game_types_by_name', $seconds, function () {
            return $this->setGameTypesByCache("name");
        })]);
      }
      /*

      Cache::add('league_tiers_by_name', $this->setLeagueTierByCache(), $seconds);

      */
      return $next($request);
    }

    private function setHeroesNameToShortCache(){
      return \GlobalFunctions::instance()->getHeroesNameToShort();
    }

    private function setHeroesByCache($by){
      return \GlobalFunctions::instance()->getHeroesIDMap($by);
    }

    private function setMapsByCache($by){
      return \GlobalFunctions::instance()->getMaps($by);
    }

    private function setRolesByCache($by){
      return \GlobalFunctions::instance()->getRoles($by);;
    }

    private function setMajorPatchCache(){
      return \GlobalFunctions::instance()->getLatestMajorPatch();
    }
    private function setAllMajorPatchCache(){
      return \GlobalFunctions::instance()->getAllMajorPatches();
    }

    private function setAllMinorPatchCache(){
      return \GlobalFunctions::instance()->getAllMinorPatches();
    }

    private function setStatColumnsCache(){
      return \GlobalFunctions::instance()->getAllStatColumns();
    }

    private function setHerolevelCache(){
      return \GlobalFunctions::instance()->getHerolevels();
    }

    private function setRoleNamesCache(){
      return \GlobalFunctions::instance()->getRoleNames();
    }

    private function setTalentDataCache(){
      return \GlobalFunctions::instance()->getTalentData();
    }

    private function setMajorMinorPatchMappingCache(){
      return \GlobalFunctions::instance()->getMajorMinorPatchMapping();
    }

    private function setLeagueTierCache(){
      return \GlobalFunctions::instance()->getLeagueTiers();
    }

    private function setSeasonDatesCache(){
      return \GlobalFunctions::instance()->getSeasonDates();
    }

    private function setLeagueBreakdownsCache(){
      return \GlobalFunctions::instance()->getLeagueTierBreakdowns();
    }

    private function setGameTypesByCache($by){
      return \GlobalFunctions::instance()->getGameTypesBy($by);
    }
    private function setLeagueTierByCache(){
      return \GlobalFunctions::instance()->getLeagueTiersByName();
    }

    private function setRegionToIntCache(){
      return \GlobalFunctions::instance()->getRegionToInt();
    }

    private function setIntToRegionCache(){
      return \GlobalFunctions::instance()->getIntToRegion();
    }

    private function setMinorPatchTwoLatest(){
      return \GlobalFunctions::instance()->getMinorPatchTwoLatest();
    }



}
