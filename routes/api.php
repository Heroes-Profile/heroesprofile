<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    // 'middleware' => 'auth:api'
], function () {
	
	Route::get('/heroes', 'HeroController@show');

	
//  Route::get('/HeroData', 'APIController@getHeroData');
  /*
  Route::get('/Heroes', 'APIController@getHeroes')->middleware('throttle:60,1');
  Route::get('/Heroes/Talents', 'APIController@getHeroesTalents')->middleware('throttle:60,1');
  Route::get('/Maps', 'APIController@getMaps')->middleware('throttle:60,1');

  Route::get('/Replay/Max', 'APIController@getMaxReplayID')->middleware('throttle:60,1');
  Route::get('/Replay/Data', 'APIController@getReplayData')->middleware('throttle:60,1');
  Route::get('/Replay/Ban', 'APIController@getReplayBans')->middleware('throttle:60,1');

  Route::get('/Heroes/Stats', 'APIController@getHeroesStats')->middleware('throttle:60,1');
  Route::get('/Heroes/Matchups', 'APIController@getHeroesMatchups')->middleware('throttle:60,1');
  Route::get('/Heroes/Talents/Details', 'APIController@getHeroesTalentsDetails')->middleware('throttle:60,1');
  Route::get('/Heroes/Talents/Builds', 'APIController@getHeroesTalentsBuilds')->middleware('throttle:60,1');

  Route::get('/Player', 'APIController@getPlayerProfileData')->middleware('throttle:60,1');
  Route::get('/Player/Replays', 'APIController@getPlayerReplayData')->middleware('throttle:60,1');
  Route::get('/Player/Hero/All', 'APIController@getPlayerHeroData')->middleware('throttle:60,1');
  Route::get('/Player/Hero/Single', 'APIController@getPlayerHeroData')->middleware('throttle:60,1');
  Route::get('/Player/Talents/Build', 'APIController@getPlayerHeroMostPlayedBuild')->middleware('throttle:60,1');

  Route::get('/Player/MMR', 'APIController@getPlayerMMR')->middleware('throttle:60,1');
  Route::get('/Player/MMR/Hero', 'APIController@getPlayerMMR')->middleware('throttle:60,1');
  Route::get('/Player/MMR/Role', 'APIController@getPlayerMMR')->middleware('throttle:60,1');

  Route::get('/Player/PreMatch/', 'APIController@getPlayerPreMatch')->middleware('throttle:60,1');

  Route::get('/NGS/Leaderboard/Highest/Average/Stat/', 'APIController@getNGSAverageStat')->middleware('throttle:60,1');
  Route::get('/NGS/Leaderboard/Highest/Total/Stat/', 'APIController@getNGSTotalStat')->middleware('throttle:60,1');
  Route::get('/NGS/Player/Hero/Stat/', 'APIController@getNGSPlayerHeroStat')->middleware('throttle:60,1');
  Route::get('/NGS/Player/Profile/', 'APIController@getNGSPlayerProfile')->middleware('throttle:60,1');
  Route::get('/NGS/Player/MMR', 'APIController@getNGSPlayerMMR')->middleware('throttle:60,1');
  Route::get('/NGS/Games/Upload/', 'APIController@uploadNGSGames')->middleware('throttle:1000,1');
  Route::post('/NGS/Games/Upload/', 'APIController@uploadNGSGames')->middleware('throttle:1000,1');
  */

});
