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


///For Testing
Route::get('getGlobalStatData', 'GlobalStatController@getData'); //For Testing Purposes.  Remove later
Route::get('getGlobalLeaderboardData', 'GlobalLeaderboardController@getData'); //For Testing Purposes.  Remove later
Route::get('getGlobalHeroStatData', 'GlobalHeroStatController@getData'); //For Testing Purposes.  Remove later
Route::get('getProfileData', 'ProfileController@getData'); //For Testing Purposes.  Remove later

Route::get('/test', 'TestController@testData'); //For Testing Purposes.  Remove later
Route::get('/', 'GlobalStatController@show');  //Defaulting to page currently being worked for ease of use.




//Post Calls to get Data for pages
Route::post('getGlobalStatData', 'GlobalStatController@getData');
Route::post('getGlobalLeaderboardData', 'GlobalLeaderboardController@getData');
Route::post('getGlobalHeroStatData', 'GlobalHeroStatController@getData');
Route::post('getProfileData', 'ProfileController@getData');



//Main Routing
//Globals
Route::get('/Global/Leaderboard', 'GlobalLeaderboardController@show');
Route::get('/Global/Stats', 'GlobalStatController@show');
Route::view('/Global/Hero/Stats', 'GlobalHeroStatController@show');

//Profile
Route::get('/Profile', 'ProfileController@show');


/*
Route::group([
    'middleware' => 'setGlobals'
], function () {

});
*/

Auth::routes();  //?? Not sure what this is exactly.  Was added by auth scaffolding.  So must do something.


//Opt Out Process
Route::view('optout', 'Optout/optout');
Route::view('optout/update/failure', 'optout/failure');
Route::view('optout/update/success', 'optout/success');
Route::get('optout/login', 'BattlenetAuthController@redirectToProvider');
Route::get('optout/success', 'BattlenetAuthController@handleOptOutProviderCallback');  //Need to switch this from login/success to optout/success in blizzard API service
