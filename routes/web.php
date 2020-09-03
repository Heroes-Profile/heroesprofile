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
Route::get('getGlobalHeroStatMapData', 'GlobalHeroStatMapController@getData'); //For Testing Purposes.  Remove later
Route::get('getGlobalHeroStatMatchupData', 'GlobalHeroStatMatchupController@getData');

Route::get('getGlobalHeroStatTalentData', 'GlobalHeroStatTalentsController@talentDetailData');
Route::get('getGlobalHeroStatTalentBuildsData', 'GlobalHeroStatTalentsController@talentBuildData');





Route::get('getProfileData', 'ProfileController@getData'); //For Testing Purposes.  Remove later

//Route::get('/test', 'TestController@testData'); //For Testing Purposes.  Remove later
//Route::get('/', 'ProfileController@show');  //Defaulting to page currently being worked for ease of use.




//Post Calls to get Data for pages
Route::post('getGlobalLeaderboardData', 'GlobalLeaderboardController@getData');
Route::post('getGlobalStatData', 'GlobalStatController@getData');
Route::post('getGlobalHeroStatMapData', 'GlobalHeroStatMapController@getData');
Route::post('getGlobalHeroStatMatchupData', 'GlobalHeroStatMatchupController@getData');
Route::post('getGlobalHeroStatTalentData', 'GlobalHeroStatTalentsController@talentDetailData');
Route::post('getGlobalHeroStatTalentBuildsData', 'GlobalHeroStatTalentsController@talentBuildData');







Route::post('getProfileData', 'ProfileController@getData');



//Main Routing
Route::get('/', 'LandingPageController@show');

//Globals
Route::get('/Global/Leaderboard', 'GlobalLeaderboardController@show');
Route::get('/Global/Stats', 'GlobalStatController@show');
Route::get('/Global/Stats/Maps', 'GlobalHeroStatMapController@show');
Route::get('/Global/Stats/Matchups', 'GlobalHeroStatMatchupController@show');
Route::get('/Global/Stats/Talents', 'GlobalHeroStatTalentsController@show');

//Profile
//Route::get('/Profile', 'ProfileController@show');


/*
Route::group([
    'middleware' => 'setGlobals'
], function () {

});
*/

//Auth::routes();
//Battlenet Login Process
Route::get('login/battlenet', 'Auth\battlenet\LoginController@show');
Route::post('authenticate/battlenet', 'BattlenetAuthController@redirectToProvider');
Route::get('authenticate/battlenet/success', 'BattlenetAuthController@handleProviderCallback');

//Opt Out Process
Route::view('optout', 'Optout/optout');
Route::view('optout/update/failure', 'optout/failure');
Route::view('optout/update/success', 'optout/success');
Route::get('optout/login', 'BattlenetAuthController@redirectToProvider');
Route::get('optout/success', 'BattlenetAuthController@handleOptOutProviderCallback');  //Need to switch this from login/success to optout/success in blizzard API service
