<?php

use Illuminate\Support\Facades\Route;

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


/*
Route::get('/', function () {
    return view('welcome');
});
*/

//Profile Data

Route::get('/Profile', 'ProfileController@show');
Route::post('getProfileData', 'ProfileController@getData');









Route::post('getGlobalStatData', 'GlobalStatController@getData');
Route::get('getGlobalStatData', 'GlobalStatController@getData'); //For Testing Purposes.  Remove later

Route::post('getGlobalLeaderboardData', 'GlobalLeaderboardController@getData');
Route::get('getGlobalLeaderboardData', 'GlobalLeaderboardController@getData'); //For Testing Purposes.  Remove later


Route::post('getGlobalHeroStatData', 'GlobalHeroStatController@getData');
Route::get('getGlobalHeroStatData', 'GlobalHeroStatController@getData'); //For Testing Purposes.  Remove later


Route::group([
    'middleware' => 'setGlobals'
], function () {
  Route::view('/', 'Global/stats');
  Route::view('/Global/Leaderboard', 'Global/leaderboard');
  Route::view('/Global/Stats', 'Global/stats');
  Route::view('/Global/Hero/Talent/Details', 'Global/Hero/Talent/details');
  Route::view('/Global/Hero/Talent/Builds', 'Global/Hero/Talent/builds');
  Route::view('/Global/Hero/Stats/Maps', 'Global/Hero/Stats/maps');
  Route::view('/Global/Hero/Stats', 'Global/Hero/stats');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/test', 'TestController@testData'); //For Testing Purposes.  Remove later


//Opt Out Process
Route::view('optout', 'Optout/optout');
Route::view('optout/update/failure', 'optout/failure');
Route::view('optout/update/success', 'optout/success');

Route::get('optout/login', 'BattlenetAuthController@redirectToProvider');

Route::get('optout/success', 'BattlenetAuthController@handleOptOutProviderCallback');  //Need to switch this from login/success to optout/success in blizzard API service
