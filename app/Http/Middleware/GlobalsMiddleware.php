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
          return \GlobalFunctions::instance()->getHeroesIDMap("id");
        })]);
      }

      if (!$request->session()->has('heroes_by_name')) {
        session(['heroes_by_name' => Cache::remember('heroes_by_name', $seconds, function () {
          return \GlobalFunctions::instance()->getHeroesIDMap("name");
        })]);
      }

      if (!$request->session()->has('heroes_name_to_short')) {
        session(['heroes_name_to_short' => Cache::remember('heroes_name_to_short', $seconds, function () {
          return \GlobalFunctions::instance()->getHeroesNameToShort();
        })]);
      }

      if (!$request->session()->has('maps_by_id')) {
        session(['maps_by_id' => Cache::remember('maps_by_id', $seconds, function () {
          return \GlobalFunctions::instance()->getMaps("map_id");
        })]);
      }

      if (!$request->session()->has('maps_by_name')) {
        session(['maps_by_name' => Cache::remember('maps_by_name', $seconds, function () {
          return \GlobalFunctions::instance()->getMaps("name");
        })]);
      }


      if (!$request->session()->has('roles_by_hero_name')) {
        session(['roles_by_hero_name' => Cache::remember('roles_by_hero_name', $seconds, function () {
          return \GlobalFunctions::instance()->getRoles("name");;
        })]);
      }

      if (!$request->session()->has('roles_by_role_name')) {
        session(['roles_by_role_name' => Cache::remember('roles_by_role_name', $seconds, function () {
          return \GlobalFunctions::instance()->getRoles("new_role");;
        })]);
      }

      if (!$request->session()->has('roles_by_id')) {
        session(['roles_by_id' => Cache::remember('roles_by_id', $seconds, function () {
          return \GlobalFunctions::instance()->getRoles("id");;
        })]);
      }

      if (!$request->session()->has('types_by_hero_name')) {
        session(['types_by_hero_name' => Cache::remember('types_by_hero_name', $seconds, function () {
          return \GlobalFunctions::instance()->getTypes("name");;
        })]);
      }

      //This is going to be a customization set by users who are logged in with their battlnet account.  Currently it is hard coded to Storm league
      if (!$request->session()->has('default_game_mode_id')) {
        session(['default_game_mode_id' => Cache::remember('default_game_mode_id', $seconds, function () {
            return 5;
        })]);
      }

      //This is going to be a customization set by users who are logged in with their battlnet account.  Currently it is hard coded to Storm league
      if (!$request->session()->has('default_game_mode_name')) {
        session(['default_game_mode_name' => Cache::remember('default_game_mode_name', $seconds, function () {
            return "Storm League";
        })]);
      }

      if (!$request->session()->has('major_patch')) {
        session(['major_patch' => Cache::remember('major_patch', $seconds, function () {
          return \GlobalFunctions::instance()->getLatestMajorPatch();
        })]);
      }

      if (!$request->session()->has('minor_patch_latest')) {
        session(['minor_patch_latest' => Cache::remember('minor_patch_latest', $seconds, function () {
          return \GlobalFunctions::instance()->getMinorPatchLatest();
        })]);
      }

      if (!$request->session()->has('all_major_patch')) {
        session(['all_major_patch' => Cache::remember('all_major_patch', $seconds, function () {
          return \GlobalFunctions::instance()->getAllMajorPatches();
        })]);
      }

      if (!$request->session()->has('all_minor_patch')) {
        session(['all_minor_patch' => Cache::remember('all_minor_patch', $seconds, function () {
          return \GlobalFunctions::instance()->getAllMinorPatches();
        })]);
      }

      if (!$request->session()->has('major_to_minor_patch_mapping')) {
        session(['major_to_minor_patch_mapping' => Cache::remember('major_to_minor_patch_mapping', $seconds, function () {
          return \GlobalFunctions::instance()->getMajorMinorPatchMapping();
        })]);
      }

      if (!$request->session()->has('stat_columns')) {
        session(['stat_columns' => Cache::remember('stat_columns', $seconds, function () {
          return \GlobalFunctions::instance()->getAllStatColumns();
        })]);
      }

      if (!$request->session()->has('hero_levels')) {
        session(['hero_levels' => Cache::remember('hero_levels', $seconds, function () {
          return \GlobalFunctions::instance()->getHerolevels();
        })]);
      }

      if (!$request->session()->has('role_names')) {
        session(['role_names' => Cache::remember('role_names', $seconds, function () {
          return \GlobalFunctions::instance()->getRoleNames();
        })]);
      }

      if (!$request->session()->has('talent_data')) {
        session(['talent_data' => Cache::remember('talent_data', $seconds, function () {
          return \GlobalFunctions::instance()->getTalentData();
        })]);
      }

      if (!$request->session()->has('season_dates')) {
        session(['season_dates' => Cache::remember('season_dates', $seconds, function () {
          return \GlobalFunctions::instance()->getSeasonDates();
        })]);
      }

      if (!$request->session()->has('latest_season')) {
        session(['latest_season' => Cache::remember('latest_season', $seconds, function () {
          return \GlobalFunctions::instance()->getLatestSeason();
        })]);
      }

      if (!$request->session()->has('leagues_breakdowns')) {
        session(['leagues_breakdowns' => Cache::remember('leagues_breakdowns', $seconds, function () {
          return \GlobalFunctions::instance()->getLeagueTierBreakdowns();
        })]);
      }

      if (!$request->session()->has('game_types_by_name')) {
        session(['game_types_by_name' => Cache::remember('game_types_by_name', $seconds, function () {
          return \GlobalFunctions::instance()->getGameTypesBy("name");
        })]);
      }
      if (!$request->session()->has('game_types_by_id')) {
        session(['game_types_by_id' => Cache::remember('game_types_by_id', $seconds, function () {
          return \GlobalFunctions::instance()->getGameTypesBy("type_id");
        })]);
      }

      if (!$request->session()->has('regions_by_id')) {
        session(['regions_by_id' => Cache::remember('regions_by_id', $seconds, function () {
          return \GlobalFunctions::instance()->getIntToRegion();
        })]);
      }

      if (!$request->session()->has('regions_by_name')) {
        session(['regions_by_name' => Cache::remember('regions_by_name', $seconds, function () {
          return \GlobalFunctions::instance()->getRegionToInt();
        })]);
      }

      if (!$request->session()->has('mmr_type_ids')) {
        session(['mmr_type_ids' => Cache::remember('mmr_type_ids', $seconds, function () {
          return \GlobalFunctions::instance()->getMMRTypeIDs();
        })]);
      }
      return $next($request);
    }
}
