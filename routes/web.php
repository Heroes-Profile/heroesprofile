<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
    'middleware' => 'setSessions'
], function () {
  Route::view('/Global/Leaderboard', 'Global/Leaderboard');

  Route::get('login', function () {
      return view('login',
        ['title' => 'Login'],
        ['paragraph' => 'This is the login page'],
      );
  });

  Route::get('/Global/Talents/Builds', 'HeroController@getHeroBuildsTableData');
  //Route::view('/', 'index');
  Route::get('/Global/Hero/', 'HeroController@show');
  Route::get('/', 'HeroController@show');


});

Route::get('/get_heroes_stats_table_data', 'HeroController@getHeroStatsTableData')->name('get_heroes_stats_table_data');
Route::get('/get_heroes_fields', 'HeroController@getFields')->name('get_heroes_fields');
