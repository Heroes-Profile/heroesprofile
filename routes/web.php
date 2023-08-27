<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MainPageController;
use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
use App\Http\Controllers\Global\GlobalLeaderboardController;
use App\Http\Controllers\Global\GlobalHeroMapStatsController;
use App\Http\Controllers\Global\GlobalHeroMatchupStatsController;

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