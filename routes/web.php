<?php

use App\Http\Controllers\AnimationsController;
use App\Http\Controllers\Auth\BattleNetController;
use App\Http\Controllers\Auth\PatreonController;
use App\Http\Controllers\BattletagSearchController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Esports\CCL\CCLController;
use App\Http\Controllers\Esports\EsportsController;
use App\Http\Controllers\Esports\HeroesInternational\HeroesInternationalController;
use App\Http\Controllers\Esports\MastersClash\MastersClashController;
use App\Http\Controllers\Esports\NGS\NGSController;
use App\Http\Controllers\Esports\NGS\NGSSingleDivisionController;
use App\Http\Controllers\Esports\NutCup\NutCupController;
use App\Http\Controllers\Esports\Other\EsportOtherController;
use App\Http\Controllers\GithubChangeController;
use App\Http\Controllers\Global\GlobalCompositionsController;
use App\Http\Controllers\Global\GlobalDraftController;
use App\Http\Controllers\Global\GlobalExtraStats;
use App\Http\Controllers\Global\GlobalHeroMapStatsController;
use App\Http\Controllers\Global\GlobalHeroMatchupsTalentsController;
use App\Http\Controllers\Global\GlobalHeroMatchupStatsController;
use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Http\Controllers\Global\GlobalLeaderboardController;
use App\Http\Controllers\Global\GlobalPartyStatsController;
use App\Http\Controllers\Global\GlobalTalentBuilderController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
use App\Http\Controllers\MainPageController;
use App\Http\Controllers\MatchPredictionGameController;
use App\Http\Controllers\Player\FriendFoeController;
use App\Http\Controllers\Player\PlayerController;
use App\Http\Controllers\Player\PlayerHeroesController;
use App\Http\Controllers\Player\PlayerMapsController;
use App\Http\Controllers\Player\PlayerMatchHistory;
use App\Http\Controllers\Player\PlayerMatchupsController;
use App\Http\Controllers\Player\PlayerMMRController;
use App\Http\Controllers\Player\PlayerRolesController;
use App\Http\Controllers\Player\PlayerTalentsController;
use App\Http\Controllers\PreMatchController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SingleMatchController;
use Illuminate\Support\Facades\Route;

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

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

// Route::middleware(['logIpAndUserAgent', 'communitySupportRedirect'])->group(function () {
Route::middleware(['logIpAndUserAgent'])->group(function () {
    Route::get('/', [MainPageController::class, 'show']);
      //Route::get('/test', [MainPageController::class, 'test']);
      //Route::get('/testJS', [MainPageController::class, 'testJS']);

    Route::redirect('/Search', '/', 301);

    Route::get('/Contact', [ContactController::class, 'show']);

    Route::get('/Privacy/Policy', [PrivacyPolicyController::class, 'show']);

    Route::get('/Github/Change/Log', [GithubChangeController::class, 'show']);

    Route::get('/battletag/searched/{userinput}/{type}', [BattletagSearchController::class, 'show']);

    // Route::get('/Compare', [CompareController::class, 'show']);
    // Route::get('/Compare/{hero}', [CompareController::class, 'show']);

    // Login
    Route::get('/Authenticate/Battlenet', [BattleNetController::class, 'show']);
    Route::get('/Battlenet/Logout', [BattleNetController::class, 'logout']);

    Route::get('/redirect/authenticate/battlenet', [BattleNetController::class, 'redirectToProvider']);
    Route::get('/authenticate/battlenet/success', [BattleNetController::class, 'handleProviderCallback']);
    Route::get('/Authenticate/Battlenet/Failed', [BattleNetController::class, 'handleProviderCallbackFailed']);

    Route::get('/authenticate/patreon', [PatreonController::class, 'redirectToProvider']);
    Route::get('/authenticate/patreon/success', [PatreonController::class, 'handleProviderCallback']);
    Route::get('/Authenticate/Patreon/Failed', [PatreonController::class, 'handleProviderCallbackFailed']);

    Route::get('/Community/Support', [MainPageController::class, 'showSupport']);

    Route::get('/Global/Hero/Maps/', [GlobalHeroMapStatsController::class, 'show']);
    Route::get('/Global/Hero/Maps/{hero}', [GlobalHeroMapStatsController::class, 'show']);
    Route::get('/Global/Hero', [GlobalHeroStatsController::class, 'show']);

    Route::get('/Global/Matchups/Talents', [GlobalHeroMatchupsTalentsController::class, 'show']);
    Route::get('/Global/Matchups/Talents/{hero}/{allyenemy}', [GlobalHeroMatchupsTalentsController::class, 'show']);

    Route::get('/Global/Matchups', [GlobalHeroMatchupStatsController::class, 'show']);
    Route::get('/Global/Matchups/{hero}', [GlobalHeroMatchupStatsController::class, 'show']);

    Route::get('/Global/Draft', [GlobalDraftController::class, 'show']);
    Route::get('/Global/Draft/{hero}', [GlobalDraftController::class, 'show']);

    Route::get('/Global/Talents/', [GlobalTalentStatsController::class, 'show']);
    Route::get('/Global/Talents/Builder', [GlobalTalentBuilderController::class, 'show']);
    Route::get('/Global/Talents/Builder/{hero}', [GlobalTalentBuilderController::class, 'show']);
    Route::get('/Global/Talents/{hero}', [GlobalTalentStatsController::class, 'show']);

    Route::get('/Global/Leaderboard/', [GlobalLeaderboardController::class, 'show']);

    Route::get('/Global/Compositions', [GlobalCompositionsController::class, 'show']);

    Route::get('/Global/Party', [GlobalPartyStatsController::class, 'show']);

    // Route::get('/Global/Extra', [GlobalExtraStats::class, 'show']); //Not sure if I want to keep this for rewrite.  Taking it out for now

    // Logged in User Settings
    Route::get('Profile/Settings', [ProfileController::class, 'showSettings'])->middleware('ensureBattlenetAuth');

    // Player data
    Route::get('Player/{battletag}/{blizz_id}/{region}', [PlayerController::class, 'show'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/FriendFoe', [FriendFoeController::class, 'show'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Hero', [PlayerHeroesController::class, 'showAll'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Hero/{hero}', [PlayerHeroesController::class, 'showSingle'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Matchups', [PlayerMatchupsController::class, 'show'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Role', [PlayerRolesController::class, 'showAll'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Role/{role}', [PlayerRolesController::class, 'showSingle'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Map', [PlayerMapsController::class, 'showAll'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Map/{map}', [PlayerMapsController::class, 'showSingle'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Talents', [PlayerTalentsController::class, 'show'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Talents/{hero}', [PlayerTalentsController::class, 'show'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/MMR', [PlayerMMRController::class, 'show'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Match/History', [PlayerMatchHistory::class, 'show'])->middleware('checkIfPrivateProfilePage');
    Route::get('Player/{battletag}/{blizz_id}/{region}/Match/Latest', [PlayerMatchHistory::class, 'showLatest'])->middleware('checkIfPrivateProfilePage');

    Route::get('Match/Single/{replayID}', [SingleMatchController::class, 'showWithoutEsport']);

    Route::get('/Match/Single/', function (Illuminate\Http\Request $request) {
        $replayID = $request->query('replayID');

        if ($replayID) {
            return redirect("/Match/Single/$replayID", 301);
        }

        return redirect('/');
    });

    Route::get('Esports/{esport}/Match/Single/{replayID}', [SingleMatchController::class, 'showWithEsport']);

    Route::get('Esports', [EsportsController::class, 'show']);

    Route::get('Esports/NGS', [NGSController::class, 'show']);
    Route::get('Esports/NGS/Division/{division}', [NGSSingleDivisionController::class, 'show']);

    Route::get('Esports/CCL', [CCLController::class, 'show']);
    Route::get('Esports/{esport}/Organization/{team}', [EsportsController::class, 'showSingleTeam']);

    Route::get('Esports/Other', [EsportOtherController::class, 'show']);
    Route::get('Esports/Other/{series}', [EsportOtherController::class, 'showSeries']);
    Route::get('Esports/Other/{series}/Team/{team}', [EsportOtherController::class, 'showSingleTeam']);
    Route::get('Esports/Other/{series}/Match/Single/{replayID}', [EsportOtherController::class, 'showWithEsport']);
    Route::get('Esports/Other/{series}/Player/{battletag}/{blizz_id}', [EsportOtherController::class, 'showPlayer']);
    Route::get('Esports/Other/{series}/Player/{battletag}/{blizz_id}/Hero/{hero}', [EsportOtherController::class, 'showPlayerHero']);
    Route::get('Esports/Other/{series}/Player/{battletag}/{blizz_id}/Map/{game_map}', [EsportOtherController::class, 'showPlayerMap']);

    Route::get('Esports/Other/{series}/Player/{battletag}/{blizz_id}/Match/History', [EsportOtherController::class, 'showPlayerMatchHistory']);
    Route::get('Esports/Other/{series}/Team/{team}/Match/History', [EsportOtherController::class, 'showTeamMatchHistory']);

    Route::get('Esports/{esport}/Team/{team}', [EsportsController::class, 'showSingleTeam']);
    Route::get('Esports/{esport}/Player/{battletag}/{blizz_id}', [EsportsController::class, 'showPlayer']);
    Route::get('Esports/{esport}/Player/{battletag}/{blizz_id}/Hero/{hero}', [EsportsController::class, 'showPlayerHero']);
    Route::get('Esports/{esport}/Player/{battletag}/{blizz_id}/Map/{game_map}', [EsportsController::class, 'showPlayerMap']);

    Route::get('Esports/{esport}/Player/{battletag}/{blizz_id}/Match/History', [EsportsController::class, 'showPlayerMatchHistory']);
    Route::get('Esports/{esport}/Team/{team}/Match/History', [EsportsController::class, 'showTeamMatchHistory']);
    Route::get('Esports/NGS/Division/{division}/Match/History', [NGSSingleDivisionController::class, 'showDivisionMatchHistory']);

    Route::get('Esports/NutCup', [NutCupController::class, 'show']);

    Route::get('Esports/MastersClash', [MastersClashController::class, 'show']);

    Route::get('Esports/HeroesInternational', [HeroesInternationalController::class, 'show']);

    Route::get('Match/Prediction/Game', [MatchPredictionGameController::class, 'show']);

    Route::get('/PreMatch/Results/', function (Illuminate\Http\Request $request) {
        $prematchID = $request->query('prematchID');

        if ($prematchID) {
            return redirect("/PreMatch/Results/$prematchID", 301);
        }

        return redirect('/');
    });
    Route::get('/PreMatch/Results/{prematchID}', [PreMatchController::class, 'show']);

});

// Ads.txt redirects
Route::redirect('/ads.txt', 'https://adstxt.venatusmedia.com/60f587eddd63d722e7e57bc1_ads.txt');
Route::redirect('/ads.txt', 'https://adstxt.venatusmedia.com/60f587eddd63d722e7e57bc1_ads.txt')->name('ads.txt');

Route::redirect('http://www.{any}/ads.txt', 'https://adstxt.venatusmedia.com/60f587eddd63d722e7e57bc1_ads.txt');
Route::redirect('http://www.{any}/ads.txt', 'https://adstxt.venatusmedia.com/60f587eddd63d722e7e57bc1_ads.txt')->name('www.ads.txt');

Route::redirect('https://www.{any}/ads.txt', 'https://adstxt.venatusmedia.com/60f587eddd63d722e7e57bc1_ads.txt');
Route::redirect('https://www.{any}/ads.txt', 'https://adstxt.venatusmedia.com/60f587eddd63d722e7e57bc1_ads.txt')->name('https.www.ads.txt');

Route::redirect('https://{any}/ads.txt', 'https://adstxt.venatusmedia.com/60f587eddd63d722e7e57bc1_ads.txt');
Route::redirect('https://{any}/ads.txt', 'https://adstxt.venatusmedia.com/60f587eddd63d722e7e57bc1_ads.txt')->name('https.ads.txt');

Route::get('/Animation/Deathwing', [AnimationsController::class, 'showDeathwing']);
Route::get('/Animation/Tassadar', [AnimationsController::class, 'showTassadar']);

Route::get('/test/patreon-earnings', [MainPageController::class, 'testPatreonEarnings']);
