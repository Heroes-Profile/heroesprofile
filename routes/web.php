<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MainPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\BattleNetController;
use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
use App\Http\Controllers\Global\GlobalLeaderboardController;
use App\Http\Controllers\Global\GlobalHeroMapStatsController;
use App\Http\Controllers\Global\GlobalHeroMatchupStatsController;
use App\Http\Controllers\Global\GlobalHeroMatchupsTalentsController;
use App\Http\Controllers\Auth\PatreonController;

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

Route::get('/', [MainPageController::class, 'show']);

//Login
Route::get('/Authenticate/Battlenet', [BattleNetController::class, 'show']);
Route::get('/redirect/authenticate/battlenet', [BattleNetController::class, 'redirectToProvider']);
Route::get('/authenticate/battlenet/success', [BattleNetController::class, 'handleProviderCallback']);

Route::get('Profile', [ProfileController::class, 'show'])->middleware('ensureBattlenetAuth');


Route::get('/authenticate/patreon', [PatreonController::class, 'redirectToProvider']);
Route::get('/authenticate/patreon/success', [PatreonController::class, 'handleProviderCallback']);




Route::get('/Global/Hero', [GlobalHeroStatsController::class, 'show']);
Route::get('/Global/Talents/', [GlobalTalentStatsController::class, 'show']);
Route::get('/Global/Talents/{hero}', [GlobalTalentStatsController::class, 'show']);
Route::get('/Global/Leaderboard', [GlobalLeaderboardController::class, 'show']);


//Global Hero Map Stats routes
Route::get('/Global/Hero/Maps/', [GlobalHeroMapStatsController::class, 'show']);
Route::get('/Global/Hero/Maps/{hero}', [GlobalHeroMapStatsController::class, 'show']);


//Global Hero Matchup Stats routes
Route::get('/Global/Matchups', [GlobalHeroMatchupStatsController::class, 'show']);
Route::get('/Global/Matchups/{hero}', [GlobalHeroMatchupStatsController::class, 'show']);

//Global Hero Matchup Talent Stats routes
Route::get('/Global/Matchups/Talents', [GlobalHeroMatchupsTalentsController::class, 'show']);





Route::get('/Global/Matchups/Talents', [GlobalHeroMatchupsTalentsController::class, 'show']);