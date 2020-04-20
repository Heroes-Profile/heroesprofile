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
Route::post('getGlobalHeroStatsData', 'GlobalHeroStatsController@getData');
Route::get('getGlobalHeroStatsData', 'GlobalHeroStatsController@getData'); //For Testing Purposes.  Remove later

Route::post('getGlobalLeaderboardData', 'GlobalLeaderboardController@getData');
Route::get('getGlobalLeaderboardData', 'GlobalLeaderboardController@getData'); //For Testing Purposes.  Remove later

Route::group([
    'middleware' => 'setGlobals'
], function () {
  Route::view('/', 'Global/stats');


  Route::view('/Global/Leaderboard', 'Global/leaderboard');
  Route::view('/Global/Stats', 'Global/stats');
  Route::view('/Global/Hero/Talent/Details', 'Global/Hero/Talent/details');
  Route::view('/Global/Hero/Talent/Builds', 'Global/Hero/Talent/builds');
  Route::view('/Global/Hero/Stats/Maps', 'Global/Hero/Stats/maps');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');



//Opt Out Process
Route::view('optout', 'optout/optout');
Route::view('optout/failure', 'optout/failure');
Route::view('optout/success', 'optout/success');

Route::get('optout/login', 'BattlenetAuthController@redirectToProvider');
