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

//Esports
use App\Http\Controllers\Esports\NGS\NGSController;
use App\Http\Controllers\Esports\NGS\NGSSingleDivisionController;
use App\Http\Controllers\Esports\EsportsController;


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
    Route::post('battletag/search', [BattletagSearchController::class, 'battletagSearch']);

    Route::post('global/hero/', [GlobalHeroStatsController::class, 'getGlobalHeroData']);

    Route::post('global/talents/', [GlobalTalentStatsController::class, 'getGlobalHeroTalentData']);

    Route::post('global/talents/build', [GlobalTalentStatsController::class, 'getGlobalHeroTalentBuildData']);

    Route::post('global/leaderboard', [GlobalLeaderboardController::class, 'getLeaderboardData']);

    Route::post('global/hero/map', [GlobalHeroMapStatsController::class, 'getHeroStatMapData']);

    Route::post('global/matchups', [GlobalHeroMatchupStatsController::class, 'getHeroMatchupData']);

    Route::post('global/matchups/talents', [GlobalHeroMatchupsTalentsController::class, 'getHeroMatchupsTalentsData']);

    Route::post('global/compositions', [GlobalCompositionsController::class, 'getCompositionsData']);

    Route::post('global/compositions/heroes', [GlobalCompositionsController::class, 'getTopHeroData']);

    Route::post('global/draft', [GlobalDraftController::class, 'getDraftData']);

    Route::post('global/party', [GlobalPartyStatsController::class, 'getPartyStats']);

    Route::post('global/extra/account/level', [GlobalExtraStats::class, 'getAccountLevelStats']);

    Route::post('global/extra/hero/level', [GlobalExtraStats::class, 'getHeroLevelStats']);

    Route::post('player', [PlayerController::class, 'getPlayerData']);

    Route::post('player/friendfoe', [FriendFoeController::class, 'getFriendFoeData']);

    Route::post('player/matchups', [PlayerMatchupsController::class, 'getMatchupData']);

    Route::post('player/heroes/all', [PlayerHeroesMapsRolesController::class, 'getData']);

    Route::post('player/heroes/single', [PlayerHeroesMapsRolesController::class, 'getData']);

    Route::post('player/roles/all/', [PlayerHeroesMapsRolesController::class, 'getData']);
    
    Route::post('/player/role/single', [PlayerHeroesMapsRolesController::class, 'getData']);


    Route::post('player/maps/all/', [PlayerHeroesMapsRolesController::class, 'getData']);

    Route::post('player/maps/single', [PlayerHeroesMapsRolesController::class, 'getData']);


    Route::post('player/talents/', [PlayerTalentsController::class, 'getPlayerTalentData']);

    Route::post('player/talents/build', [PlayerTalentsController::class, 'getPlayerTalentData']);

    Route::post('player/mmr', [PlayerMMRController::class, 'getData']);


    Route::post('match/single', [SingleMatchController::class, 'getData']);

    Route::post('profile/save/settings', [ProfileController::class, 'saveSettings']);

    Route::post('player/match/history', [PlayerMatchHistory::class, 'getData']);

    Route::post('player/match/history', [PlayerMatchHistory::class, 'getData']);

    Route::post('esports/ngs/standings', [NGSController::class, 'getStandingData']);
    Route::post('esports/ngs/divisions', [NGSController::class, 'getDivisionData']);
    Route::post('esports/ngs/teams', [NGSController::class, 'getTeamsData']);
    Route::post('esports/ngs/player/search', [NGSController::class, 'playerSearch']);
    Route::post('esports/ngs/matches', [NGSController::class, 'getRecentMatchData']);
    Route::post('esports/ngs/hero/stats', [NGSController::class, 'getOverallHeroStats']);
    Route::post('esports/ngs/hero/talents/stats', [NGSController::class, 'getOverallTalentStats']);

    Route::post('esports/ngs/division/single', [NGSSingleDivisionController::class, 'getSingleDivisionData']);


    Route::post('esports/single/team', [EsportsController::class, 'getSingleTeamData']);


});