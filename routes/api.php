<?php

use App\Http\Controllers\BattletagSearchController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\Esports\CCL\CCLController;
//Global
use App\Http\Controllers\Esports\EsportsController;
use App\Http\Controllers\Esports\HeroesInternational\HeroesInternationalController;
use App\Http\Controllers\Esports\MastersClash\MastersClashController;
use App\Http\Controllers\Esports\NGS\NGSController;
use App\Http\Controllers\Esports\NGS\NGSSingleDivisionController;
use App\Http\Controllers\Global\GlobalCompositionsController;
use App\Http\Controllers\Global\GlobalDraftController;
use App\Http\Controllers\Global\GlobalExtraStats;
use App\Http\Controllers\Global\GlobalHeroMapStatsController;
use App\Http\Controllers\Global\GlobalHeroMatchupsTalentsController;
use App\Http\Controllers\Global\GlobalHeroMatchupStatsController;
use App\Http\Controllers\Global\GlobalHeroStatsController;
//Player
use App\Http\Controllers\Global\GlobalLeaderboardController;
use App\Http\Controllers\Global\GlobalPartyStatsController;
use App\Http\Controllers\Global\GlobalTalentBuilderController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
use App\Http\Controllers\Player\FriendFoeController;
use App\Http\Controllers\Player\PlayerController;
use App\Http\Controllers\Player\PlayerHeroesMapsRolesController;
//Profile
use App\Http\Controllers\Player\PlayerMatchHistory;
//Esports
use App\Http\Controllers\Player\PlayerMatchupsController;
use App\Http\Controllers\Player\PlayerMMRController;
use App\Http\Controllers\Player\PlayerTalentsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SingleMatchController;
use Illuminate\Support\Facades\Route;

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

    Route::post('global/talents/builder', [GlobalTalentBuilderController::class, 'getData']);

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

    Route::post('player/find/max/stat/match', [PlayerHeroesMapsRolesController::class, 'findMatch']);

    Route::post('player/talents/', [PlayerTalentsController::class, 'getPlayerTalentData']);

    Route::post('player/talents/build', [PlayerTalentsController::class, 'getPlayerTalentData']);

    Route::post('player/mmr', [PlayerMMRController::class, 'getData']);

    Route::post('match/single', [SingleMatchController::class, 'getData']);

    Route::post('profile/save/settings', [ProfileController::class, 'saveSettings']);
    Route::post('profile/remove/patreon', [ProfileController::class, 'removePatreon']);
    Route::post('profile/set/account/visibility', [ProfileController::class, 'setAccountVisibility']);

    Route::post('player/match/history', [PlayerMatchHistory::class, 'getData']);

    Route::post('player/match/history', [PlayerMatchHistory::class, 'getData']);

    Route::post('esports/ngs/standings', [NGSController::class, 'getStandingData']);
    Route::post('esports/ngs/divisions', [NGSController::class, 'getDivisionData']);
    Route::post('esports/ngs/teams', [NGSController::class, 'getTeamsData']);
    Route::post('esports/ngs/player/search', [NGSController::class, 'playerSearch']);
    Route::post('esports/ngs/matches', [EsportsController::class, 'getRecentMatchData']);
    Route::post('esports/ngs/hero/stats', [EsportsController::class, 'getOverallHeroStats']);
    Route::post('esports/ngs/hero/talents/stats', [EsportsController::class, 'getOverallTalentStats']);

    Route::post('esports/ngs/division/single', [NGSSingleDivisionController::class, 'getSingleDivisionData']);

    Route::post('esports/single/team', [EsportsController::class, 'getData']);
    Route::post('esports/single/player', [EsportsController::class, 'getData']);
    Route::post('esports/single/player/hero', [EsportsController::class, 'getData']);
    Route::post('esports/single/player/map', [EsportsController::class, 'getData']);

    Route::post('esports/ccl/organizations', [CCLController::class, 'getOrganizationData']);
    Route::post('esports/ccl/matches', [EsportsController::class, 'getRecentMatchData']);

    Route::post('esports/ccl/hero/stats', [EsportsController::class, 'getOverallHeroStats']);
    Route::post('esports/ccl/hero/talents/stats', [EsportsController::class, 'getOverallTalentStats']);

    Route::post('esports/nutcup/hero/stats', [EsportsController::class, 'getOverallHeroStats']);
    Route::post('esports/nutcup/hero/talents/stats', [EsportsController::class, 'getOverallTalentStats']);

    Route::post('esports/mastersclash/teams', [MastersClashController::class, 'getTeamsData']);
    Route::post('esports/mastersclash/matches', [MastersClashController::class, 'getRecentMatchData']);
    Route::post('esports/mastersclash/hero/stats', [EsportsController::class, 'getOverallHeroStats']);
    Route::post('esports/mastersclash/hero/talents/stats', [EsportsController::class, 'getOverallTalentStats']);

    Route::post('esports/heroesinternational/teams', [HeroesInternationalController::class, 'getTeamsData']);
    Route::post('esports/heroesinternational/matches', [HeroesInternationalController::class, 'getRecentMatchData']);
    Route::post('esports/heroesinternational/hero/stats', [EsportsController::class, 'getOverallHeroStats']);
    Route::post('esports/heroesinternational/hero/talents/stats', [EsportsController::class, 'getOverallTalentStats']);

    Route::post('compare', [CompareController::class, 'getData']);

});
