<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MainPageController;
use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
use App\Http\Controllers\Global\GlobalLeaderboardController;
use App\Http\Controllers\Global\GlobalHeroMapStatsController;

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
Route::get('/Global/Hero/Maps', [GlobalHeroMapStatsController::class, 'show']);