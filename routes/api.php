<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GeneralDataController;
use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
use App\Http\Controllers\Global\GlobalLeaderboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/




Route::prefix('v1')->group(function () {
    Route::get('battletag/search', [GeneralDataController::class, 'battletagSearch']); //testing
    Route::post('battletag/search', [GeneralDataController::class, 'battletagSearch']);


    Route::get('global/hero/', [GlobalHeroStatsController::class, 'getGlobalHeroData']); //testing
    Route::post('global/hero/', [GlobalHeroStatsController::class, 'getGlobalHeroData']);

    Route::get('global/talents/', [GlobalTalentStatsController::class, 'getGlobalHeroTalentData']); //testing
    Route::post('global/talents/', [GlobalTalentStatsController::class, 'getGlobalHeroTalentData']);

    Route::get('global/talents/build', [GlobalTalentStatsController::class, 'getGlobalHeroTalentBuildData']); //testing
    Route::post('global/talents/build', [GlobalTalentStatsController::class, 'getGlobalHeroTalentBuildData']);

    Route::get('global/leaderboard', [GlobalLeaderboardController::class, 'getLeaderboardData']); //testing
    Route::post('global/leaderboard', [GlobalLeaderboardController::class, 'getLeaderboardData']);
});