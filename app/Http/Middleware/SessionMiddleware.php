<?php

namespace App\Http\Middleware;

use Closure;

class SessionMiddleware
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
      if (!$request->session()->has('heroes_by_id')) {
        session(['heroes_by_id' => $this->setHeroesBySession("id")]);
      }

      if (!$request->session()->has('heroes_by_name')) {
        session(['heroes_by_name' => $this->setHeroesBySession("name")]);
      }

      if (!$request->session()->has('heroes_name_to_short')) {
        session(['heroes_name_to_short' => $this->setHeroesNameToShortSession()]);
      }


      if (!$request->session()->has('maps_by_id')) {
        session(['maps_by_id' => $this->setMapsBySession("map_id")]);
      }

      if (!$request->session()->has('maps_by_name')) {
        session(['maps_by_name' => $this->setMapsBySession("name")]);
      }


      if (!$request->session()->has('maps_by_name_filter_format')) {
        if (!$request->session()->has('maps_by_name')) {
          session(['maps_by_name' => $this->setMapsBySession("name")]);
        }
        session(['maps_by_name_filter_format' => $this->setMapsFilterFormatSession($request->session()->get('maps_by_name'))]);
      }

      if (!$request->session()->has('roles_by_name')) {
        session(['roles_by_name' => $this->setRolesBySession("name")]);
      }

      if (!$request->session()->has('default_game_mode_id')) {
        session(['default_game_mode_id' => 5]);
      }

      if (!$request->session()->has('default_game_mode_name')) {
        session(['default_game_mode_name' => "Storm League"]);
      }

      if (!$request->session()->has('major_patch')) {
        session(['major_patch' => $this->setMajorPatchSession()]);
      }

      if (!$request->session()->has('all_major_patch')) {
        session(['all_major_patch' => $this->setAllMajorPatchSession()]);
      }

      if (!$request->session()->has('all_minor_patch')) {
        session(['all_minor_patch' => $this->setAllMinorPatchSession()]);
      }

      if (!$request->session()->has('major_to_minor_patch_mapping')) {
        session(['major_to_minor_patch_mapping' => $this->setMajorMinorPatchMappingSession()]);
      }

      if (!$request->session()->has('stat_columns')) {
        session(['stat_columns' => $this->setStatColumnsSession()]);
      }

      if (!$request->session()->has('hero_levels')) {
        session(['hero_levels' => $this->setHerolevelSession()]);
      }

      if (!$request->session()->has('role_names')) {
        session(['role_names' => $this->setRoleNamesSession()]);
      }

      if (!$request->session()->has('talent_data')) {
        session(['talent_data' => $this->setTalentDataSession()]);
      }

      if (!$request->session()->has('league_tiers')) {
        session(['league_tiers' => $this->setLeagueTierSession()]);
      }

      if (!$request->session()->has('season_dates')) {
        session(['season_dates' => $this->setSeasonDatesSession()]);
      }

      return $next($request);
    }
    private function setMapsFilterFormatSession($maps){
      $return_data = array();
      $counter = 0;
      foreach ($maps as $key => $value){
        $data = array();
        $data["key"] = $key;
        $data["value"] = $value;
        $return_data[$counter] = $data;
        $counter++;
      }
      return $return_data;
    }

    private function setHeroesNameToShortSession(){
      return \GlobalFunctions::instance()->getHeroesNameToShort();
    }

    private function setHeroesBySession($by){
      return \GlobalFunctions::instance()->getHeroesIDMap($by);
    }

    private function setMapsBySession($by){
      return \GlobalFunctions::instance()->getMaps($by);
    }

    private function setRolesBySession($by){
      return \GlobalFunctions::instance()->getRoles($by);;
    }

    private function setMajorPatchSession(){
      return \GlobalFunctions::instance()->getLatestMajorPatch();
    }
    private function setAllMajorPatchSession(){
      return \GlobalFunctions::instance()->getAllMajorPatches();
    }

    private function setAllMinorPatchSession(){
      return \GlobalFunctions::instance()->getAllMinorPatches();
    }

    private function setStatColumnsSession(){
      return \GlobalFunctions::instance()->getAllStatColumns();
    }

    private function setHerolevelSession(){
      return \GlobalFunctions::instance()->getHerolevels();
    }

    private function setRoleNamesSession(){
      return \GlobalFunctions::instance()->getRoleNames();
    }

    private function setTalentDataSession(){
      return \GlobalFunctions::instance()->getTalentData();
    }

    private function setMajorMinorPatchMappingSession(){
      return \GlobalFunctions::instance()->getMajorMinorPatchMapping();
    }

    private function setLeagueTierSession(){
      return \GlobalFunctions::instance()->getLeagueTiers();
    }

    private function setSeasonDatesSession(){
      return \GlobalFunctions::instance()->getSeasonDates();
    }


}
