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

///For Testing
Route::get('getGlobalStatData', 'GlobalStatController@getData'); //For Testing Purposes.  Remove later
Route::get('getGlobalLeaderboardData', 'GlobalLeaderboardController@getData'); //For Testing Purposes.  Remove later
Route::get('getGlobalHeroStatMapData', 'GlobalHeroStatMapController@getData'); //For Testing Purposes.  Remove later
Route::get('getGlobalHeroStatMatchupData', 'GlobalHeroStatMatchupController@getData'); //For Testing Purposes.  Remove later
Route::get('getGlobalHeroStatTalentData', 'GlobalHeroStatTalentsController@talentDetailData'); //For Testing Purposes.  Remove later
Route::get('getGlobalHeroStatTalentBuildsData', 'GlobalHeroStatTalentsController@talentBuildData'); //For Testing Purposes.  Remove later
Route::get('getProfileData', 'ProfileController@getProfileData'); //For Testing Purposes.  Remove later
Route::get('profile/getFriendAndFoeData', 'ProfileController@getFriendsAndFoesData');//For Testing Purposes.  Remove later
Route::get('profile/getHeroAllData', 'ProfileController@getHeroAllData');//For Testing Purposes.  Remove later
Route::get('profile/getMapAllData', 'ProfileController@getMapAllData');//For Testing Purposes.  Remove later
Route::get('profile/getMatchHistoryData', 'ProfileController@getMatchHistoryData');//For Testing Purposes.  Remove later
Route::get('profile/getMatchupsData', 'ProfileController@getMatchupsData');//For Testing Purposes.  Remove later
Route::get('profile/getMMRData', 'ProfileController@getMMRData');//For Testing Purposes.  Remove later
Route::get('profile/getRoleAllData', 'ProfileController@getRoleAllData');//For Testing Purposes.  Remove later
Route::get('profile/getTalentsData', 'ProfileController@getTalentsData');//For Testing Purposes.  Remove later
Route::get('authenticate/battlenet', 'BattlenetAuthController@redirectToProvider');//For Testing Purposes.  Remove later


//Post Calls to get Data for pages
Route::post('getGlobalLeaderboardData', 'GlobalLeaderboardController@getData');
Route::post('getGlobalStatData', 'GlobalStatController@getData');
Route::post('getGlobalHeroStatMapData', 'GlobalHeroStatMapController@getData');
Route::post('getGlobalHeroStatMatchupData', 'GlobalHeroStatMatchupController@getData');
Route::post('getGlobalHeroStatTalentData', 'GlobalHeroStatTalentsController@talentDetailData');
Route::post('getGlobalHeroStatTalentBuildsData', 'GlobalHeroStatTalentsController@talentBuildData');
Route::post('getProfileData', 'ProfileController@getProfileData');
Route::post('profile/getFriendAndFoeData', 'ProfileController@getFriendsAndFoesData');
Route::post('profile/getHeroAllData', 'ProfileController@getHeroAllData');
Route::post('profile/getMapAllData', 'ProfileController@getMapAllData');
Route::post('profile/getMatchHistory', 'ProfileController@getMatchHistory');
Route::post('profile/getMatchups', 'ProfileController@getMatchups');
Route::post('profile/getMMR', 'ProfileController@getMMR');
Route::post('profile/getRoleAllData', 'ProfileController@getRoleAllData');
Route::post('profile/getTalentsData', 'ProfileController@getTalentsData');


//Main Routing
Route::get('/', 'LandingPageController@show');
Route::get('/search', 'SearchController@show');
Route::get('/Account', 'AccountController@show');

//Global Stats
Route::get('/Global/Leaderboard', 'GlobalLeaderboardController@show');
Route::get('/Global/Stats', 'GlobalStatController@show');
Route::get('/Global/Stats/Maps', 'GlobalHeroStatMapController@show');
Route::get('/Global/Stats/Matchups', 'GlobalHeroStatMatchupController@show');
Route::get('/Global/Stats/Talents', 'GlobalHeroStatTalentsController@show');

//Profile
Route::get('/Profile', 'ProfileController@profile');
Route::get('/Profile/FriendsAndFoes', 'ProfileController@friendsAndFoes');
Route::get('/Profile/Hero/All', 'ProfileController@heroAll');
Route::get('/Profile/Map/All', 'ProfileController@mapAll');
Route::get('/Profile/Match/History', 'ProfileController@matchHistory');
Route::get('/Profile/Matchups', 'ProfileController@matchups');
Route::get('/Profile/MMR', 'ProfileController@mmr');
Route::get('/Profile/Role/All', 'ProfileController@roleAll');
Route::get('/Profile/Talents', 'ProfileController@talents');


//Match Page
Route::get('/Match/Single', 'MatchController@show');
//Route::get('getSingeMatchData', 'MatchController@getMatchData');
//Route::post('getSingeMatchData', 'MatchController@getMatchData');
*/

//Drafter
Route::get('/', 'DraftController@show');
Route::get('/Drafter', 'DraftController@show');

//Bans
Route::get('/getDraftBanData', 'DraftController@getDraftBanData');
Route::post('/getDraftBanData', 'DraftController@getDraftBanData');

//Initial Picks
Route::get('/getInitialDraftData', 'DraftController@getInitialData');
Route::post('/getInitialDraftData', 'DraftController@getInitialData');

//Composition Picks
Route::get('/getCompositionData', 'DraftController@getCompositionData');
Route::post('/getCompositionData', 'DraftController@getCompositionData');



/*
Route::group([
    'middleware' => 'setGlobals'
], function () {

});
*/
/*
//Battlenet Login/Logout Process
Route::get('login/battlenet', 'Auth\battlenet\LoginController@show');
Route::get('logout/battlenet', 'Auth\battlenet\LogoutController@show');
Route::post('authenticate/battlenet', 'BattlenetAuthController@redirectToProvider');
Route::get('authenticate/battlenet/success', 'BattlenetAuthController@handleProviderCallback');


//Opt Out Process
Route::view('/Account/Optout/', 'Account/optout/optout');
Route::post('/account/optout/save', 'AccountController@optout');

/**
 * Gamedata Routes
 */
Route::get('/Gamedata', 'GamedataController@heroes');
Route::get('/Gamedata/Heroes', 'GamedataController@heroes');
Route::get('/Gamedata/Heroes/{id}', 'GamedataController@hero');

