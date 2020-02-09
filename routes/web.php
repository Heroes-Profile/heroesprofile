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
    'middleware' => 'setGlobals'
], function () {
  //Route::get('/Profile', 'ProfileController@show');
/*
  Route::view('/Profiledata', 'Profile.home');
  Route::get('/Profile', 'ProfileController@show');
  Route::view('/Profile/FriendsFoes', 'Profile/FriendsFoes/home');
  Route::view('/Profile/Heroes/All', 'Profile/Heroes/All/home');
  Route::view('/Profile/Heroes/Single', 'Profile/Heroes/Single/home');





Route::get('login', 'LoginController@redirectToProvider');
Route::get('login/success', 'LoginController@handleProviderCallback');


  Route::get('/Global/Talents/Builds', 'HeroController@getHeroBuildsTableData');
  //Route::view('/', 'index');
  Route::get('/Global/Hero/', 'HeroController@show');


  */
  Route::view('/Test', 'Test');
  Route::get('/', 'HeroController@show');


  /*
    Route::get('login', function () {
        return view('login',
          ['title' => 'Login'],
          ['paragraph' => 'This is the login page'],
        );
    });
  */


});

Route::post('/get_heroes_stats_table_data', 'HeroController@getHeroStatsTableData')->name('get_heroes_stats_table_data');


Route::get('/get_heroes_fields', 'HeroController@getFields')->name('get_heroes_fields');

//Auth::routes();
//Auth::routes(['register' => false]);

//Route::get('/home', 'HomeController@index')->name('home');
