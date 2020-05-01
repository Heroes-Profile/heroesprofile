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
      //UNUSED FOR NOW
      
      /*
      if (!$request->session()->has('all_minor_patch')) {
        session(['all_minor_patch' => Cache::remember('all_minor_patch', getCacheTimeGlobals(), function () {
          return getAllMinorPatches();
        })]);
      }

      if (!$request->session()->has('season_dates')) {
        session(['season_dates' => Cache::remember('season_dates', getCacheTimeGlobals(), function () {
          return getSeasonDates();
        })]);
      }

      if (!$request->session()->has('latest_season')) {
        session(['latest_season' => Cache::remember('latest_season', getCacheTimeGlobals(), function () {
          return getLatestSeason();
        })]);
      }

      //This is going to be a customization set by users who are logged in with their battlnet account.  Currently it is hard coded to Storm league
      if (!$request->session()->has('default_game_mode_id')) {
        session(['default_game_mode_id' => Cache::remember('default_game_mode_id', getCacheTimeGlobals(), function () {
            return 5;
        })]);
      }

      if (!$request->session()->has('regions_by_id')) {
        session(['regions_by_id' => Cache::remember('regions_by_id', getCacheTimeGlobals(), function () {
          return getIntToRegion();
        })]);
      }

      if (!$request->session()->has('heroes_by_name_to_short')) {
        session(['heroes_by_name' => Cache::remember('heroes_by_name', getCacheTimeGlobals(), function () {
          return getHeroesIDMap("name", "short_name");
        })]);
      }
      */


      return $next($request);
    }
}
