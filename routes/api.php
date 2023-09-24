<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



use App\Http\Controllers\BattletagSearchController;
use App\Http\Controllers\SingleMatchController;



//Global
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



//Player
use App\Http\Controllers\Player\PlayerController;
use App\Http\Controllers\Player\FriendFoeController;
use App\Http\Controllers\Player\PlayerHeroesController;
use App\Http\Controllers\Player\PlayerMatchupsController;
use App\Http\Controllers\Player\PlayerHeroesMapsRolesController;
use App\Http\Controllers\Player\PlayerTalentsController;
use App\Http\Controllers\Player\PlayerMMRController;
use App\Http\Controllers\Player\PlayerMatchHistory;


//Profile
use App\Http\Controllers\ProfileController;

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
    Route::get('battletag/search', [BattletagSearchController::class, 'battletagSearch']); //testing
    Route::post('battletag/search', [BattletagSearchController::class, 'battletagSearch']);


    Route::get('global/hero/', [GlobalHeroStatsController::class, 'getGlobalHeroData']); //testing
    Route::post('global/hero/', [GlobalHeroStatsController::class, 'getGlobalHeroData']);

    Route::get('global/talents/', [GlobalTalentStatsController::class, 'getGlobalHeroTalentData']); //testing
    Route::post('global/talents/', [GlobalTalentStatsController::class, 'getGlobalHeroTalentData']);

    Route::get('global/talents/build', [GlobalTalentStatsController::class, 'getGlobalHeroTalentBuildData']); //testing
    Route::post('global/talents/build', [GlobalTalentStatsController::class, 'getGlobalHeroTalentBuildData']);

    Route::get('global/leaderboard', [GlobalLeaderboardController::class, 'getLeaderboardData']); //testing
    Route::post('global/leaderboard', [GlobalLeaderboardController::class, 'getLeaderboardData']);

    Route::get('global/hero/map', [GlobalHeroMapStatsController::class, 'getHeroStatMapData']); //testing
    Route::post('global/hero/map', [GlobalHeroMapStatsController::class, 'getHeroStatMapData']);


    Route::get('global/matchups', [GlobalHeroMatchupStatsController::class, 'getHeroMatchupData']); //testing
    Route::post('global/matchups', [GlobalHeroMatchupStatsController::class, 'getHeroMatchupData']);


    Route::get('global/matchups/talents', [GlobalHeroMatchupsTalentsController::class, 'getHeroMatchupsTalentsData']); //testing
    Route::post('global/matchups/talents', [GlobalHeroMatchupsTalentsController::class, 'getHeroMatchupsTalentsData']);


    Route::get('global/compositions', [GlobalCompositionsController::class, 'getCompositionsData']); //testing
    Route::post('global/compositions', [GlobalCompositionsController::class, 'getCompositionsData']);

    Route::get('global/compositions/heroes', [GlobalCompositionsController::class, 'getTopHeroData']); //testing
    Route::post('global/compositions/heroes', [GlobalCompositionsController::class, 'getTopHeroData']);


    Route::get('global/draft', [GlobalDraftController::class, 'getDraftData']); //testing
    Route::post('global/draft', [GlobalDraftController::class, 'getDraftData']);

    Route::get('global/party', [GlobalPartyStatsController::class, 'getPartyStats']); //testing
    Route::post('global/party', [GlobalPartyStatsController::class, 'getPartyStats']);

    Route::get('global/extra/account/level', [GlobalExtraStats::class, 'getAccountLevelStats']); //testing
    Route::post('global/extra/account/level', [GlobalExtraStats::class, 'getAccountLevelStats']);

    Route::get('global/extra/hero/level', [GlobalExtraStats::class, 'getHeroLevelStats']); //testing
    Route::post('global/extra/hero/level', [GlobalExtraStats::class, 'getHeroLevelStats']);



    Route::get('player', [PlayerController::class, 'getPlayerData']); //testing
    Route::post('player', [PlayerController::class, 'getPlayerData']);

    Route::get('player/friendfoe', [FriendFoeController::class, 'getFriendFoeData']); //testing
    Route::post('player/friendfoe', [FriendFoeController::class, 'getFriendFoeData']);



    Route::get('player/matchups', [PlayerMatchupsController::class, 'getMatchupData']); //testing
    Route::post('player/matchups', [PlayerMatchupsController::class, 'getMatchupData']);



    Route::get('player/heroes/all', [PlayerHeroesMapsRolesController::class, 'getData']); //testing
    Route::post('player/heroes/all', [PlayerHeroesMapsRolesController::class, 'getData']);

    Route::get('player/heroes/single', [PlayerHeroesMapsRolesController::class, 'getData']); //testing
    Route::post('player/heroes/single', [PlayerHeroesMapsRolesController::class, 'getData']);

    Route::get('player/roles/all/', [PlayerHeroesMapsRolesController::class, 'getData']); //testing
    Route::post('player/roles/all/', [PlayerHeroesMapsRolesController::class, 'getData']);


    Route::get('player/maps/all/', [PlayerHeroesMapsRolesController::class, 'getData']); //testing
    Route::post('player/maps/all/', [PlayerHeroesMapsRolesController::class, 'getData']);

    Route::get('player/maps/single', [PlayerHeroesMapsRolesController::class, 'getData']); //testing
    Route::post('player/maps/single', [PlayerHeroesMapsRolesController::class, 'getData']);


    Route::get('player/talents/', [PlayerTalentsController::class, 'getPlayerTalentData']); //testing
    Route::post('player/talents/', [PlayerTalentsController::class, 'getPlayerTalentData']);

    Route::get('player/talents/build', [PlayerTalentsController::class, 'getPlayerTalentData']); //testing
    Route::post('player/talents/build', [PlayerTalentsController::class, 'getPlayerTalentData']);

    Route::get('player/mmr', [PlayerMMRController::class, 'getData']); //testing
    Route::post('player/mmr', [PlayerMMRController::class, 'getData']);


    Route::get('match/single', [SingleMatchController::class, 'getData']); //testing
    Route::post('match/single', [SingleMatchController::class, 'getData']);

    Route::get('profile/save/settings', [ProfileController::class, 'saveSettings']); //testing
    Route::post('profile/save/settings', [ProfileController::class, 'saveSettings']);

    Route::get('player/match/history', [PlayerMatchHistory::class, 'getData']); //testing
    Route::post('player/match/history', [PlayerMatchHistory::class, 'getData']);





});