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
  Route::view('/', 'layouts.app');

  //This route gives the correct object/data to use for the replacement code for getHeroStatsTableData.
  //But the return is likely not what is expected so not changing it yet
  Route::view('/Global/Hero', 'Global/Hero/test');
});

Route::view('optout', 'optout/optout');
Route::get('optout/login', 'BattlenetAuthController@redirectToProvider');
Route::view('optout/failure', 'optout/failure');
Route::view('optout/success', 'optout/success');

//Need to create alternate route/blizz API later.  Using this one for opt out for now.
Route::get('login/success', 'BattlenetAuthController@handleOptOutProviderCallback');



//Likely need to remove these and throw them as the API call.
//Right now the API call just calls the HeroController @show method, but makes more sense to just use the API call to ask for discrete data 
//similiar to how I am getting/returning data from view Global/Hero/test.blade.php
Route::post('/get_heroes_stats_table_data', 'HeroController@getHeroStatsTableData')->name('get_heroes_stats_table_data');
Route::get('/get_heroes_fields', 'HeroController@getFields')->name('get_heroes_fields');
