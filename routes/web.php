<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\BattleNetController;
use App\Http\Controllers\Auth\PatreonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Player\PlayerController;
use App\Http\Controllers\Player\FriendFoeController;
use App\Http\Controllers\Player\PlayerHeroesController;

use App\Http\Controllers\MainPageController;
use App\Http\Controllers\GamedataController;
use App\Http\Controllers\CompareController;



use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
use App\Http\Controllers\Global\GlobalLeaderboardController;
use App\Http\Controllers\Global\GlobalHeroMapStatsController;
use App\Http\Controllers\Global\GlobalHeroMatchupStatsController;
use App\Http\Controllers\Global\GlobalHeroMatchupsTalentsController;
use App\Http\Controllers\Global\GlobalCompositionsController;
use App\Http\Controllers\Global\GlobalDraftController;
use App\Http\Controllers\Global\GlobalPartyStatsController;
use App\Http\Controllers\Global\GlobalExtraStats;
 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

///The way we are defining url paths to be consistent with the current site, the order of these routes cannot change.

Route::get('/', [MainPageController::class, 'show']);
Route::get('/Compare', [CompareController::class, 'show']);


//Login
Route::get('/Authenticate/Battlenet', [BattleNetController::class, 'show']);
Route::get('/Battlenet/Logout', [BattleNetController::class, 'logout']);





Route::get('/redirect/authenticate/battlenet', [BattleNetController::class, 'redirectToProvider']);
Route::get('/authenticate/battlenet/success', [BattleNetController::class, 'handleProviderCallback']);



Route::get('/authenticate/patreon', [PatreonController::class, 'redirectToProvider']);
Route::get('/authenticate/patreon/success', [PatreonController::class, 'handleProviderCallback']);


Route::get('/Global/Hero/Maps/', [GlobalHeroMapStatsController::class, 'show']);
Route::get('/Global/Hero/Maps/{hero}', [GlobalHeroMapStatsController::class, 'show']);
Route::get('/Global/Hero', [GlobalHeroStatsController::class, 'show']);



Route::get('/Global/Matchups/Talents', [GlobalHeroMatchupsTalentsController::class, 'show']);
Route::get('/Global/Matchups/Talents/{hero}', [GlobalHeroMatchupsTalentsController::class, 'show']);
Route::get('/Global/Matchups', [GlobalHeroMatchupStatsController::class, 'show']);
Route::get('/Global/Matchups/{hero}', [GlobalHeroMatchupStatsController::class, 'show']);


Route::get('/Global/Draft/General', [GlobalDraftController::class, 'show']);
Route::get('/Global/Draft/General/{hero}', [GlobalDraftController::class, 'show']);


Route::get('/Global/Talents/', [GlobalTalentStatsController::class, 'show']);
Route::get('/Global/Talents/{hero}', [GlobalTalentStatsController::class, 'show']);



Route::get('/Global/Leaderboard', [GlobalLeaderboardController::class, 'show']);

Route::get('/Global/Compositions', [GlobalCompositionsController::class, 'show']);



Route::get('/Global/Party', [GlobalPartyStatsController::class, 'show']);


Route::get('/Global/Extra', [GlobalExtraStats::class, 'show']);



//Logged in User Settings
Route::get('Profile/Settings', [ProfileController::class, 'showSettings'])->middleware('ensureBattlenetAuth');

//Player data
Route::get('Player/FriendFoe/{battletag}/{blizz_id}/{region}', [FriendFoeController::class, 'show']);
Route::get('Player/Hero/All/{battletag}/{blizz_id}/{region}', [PlayerHeroesController::class, 'showAll']);
Route::get('Player/Hero/Single/{battletag}/{blizz_id}/{region}/{hero}', [PlayerHeroesController::class, 'showSingle']);



Route::get('Player/{battletag}/{blizz_id}/{region}', [PlayerController::class, 'show']);








//Rewrite game data later
Route::get('/Gamedata', [GamedataController::class, 'heroes']);
Route::get('/Gamedata/Heroes', [GamedataController::class, 'heroes']);
Route::get('/Gamedata/Heroes/{id}', [GamedataController::class, 'hero']);

