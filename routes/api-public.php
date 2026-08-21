<?php

use App\Http\Controllers\Api\Public\MatchController;
use App\Http\Controllers\Api\Public\PlayerController;
use App\Http\Controllers\Api\Public\ReferenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API v1
|--------------------------------------------------------------------------
|
| Mounted twice by RouteServiceProvider: on the API subdomain, and under
| `api/public/v1` so it is reachable before DNS moves.
|
| Every endpoint carries both keyed middlewares. `api.fixtures` serves example
| data to accounts that have not activated live data; `api.quota` charges the
| ones that have. `php artisan api:check-fixtures` fails if the two get out of
| step or a fixture file is missing.
|
*/

Route::get('maps', [ReferenceController::class, 'maps'])
    ->middleware(['api.fixtures:maps', 'api.quota:maps'])
    ->name('api.public.maps');

Route::get('heroes', [ReferenceController::class, 'heroes'])
    ->middleware(['api.fixtures:heroes', 'api.quota:heroes'])
    ->name('api.public.heroes');

Route::get('heroes/talents', [ReferenceController::class, 'talents'])
    ->middleware(['api.fixtures:heroes_talents', 'api.quota:heroes_talents'])
    ->name('api.public.heroes.talents');

Route::get('patches', [ReferenceController::class, 'patches'])
    ->middleware(['api.fixtures:patches', 'api.quota:patches'])
    ->name('api.public.patches');

Route::get('mmr/tier', [ReferenceController::class, 'mmrTier'])
    ->middleware(['api.fixtures:mmr_tier', 'api.quota:mmr_tier'])
    ->name('api.public.mmr.tier');

/*
| Player endpoints. Identified by `battletag` and `region` in the query string,
| the same pair the old API took; blizz_id is resolved server-side.
*/

Route::get('players', [PlayerController::class, 'profile'])
    ->middleware(['api.fixtures:player', 'api.quota:player'])
    ->name('api.public.players');

Route::get('players/matches', [PlayerController::class, 'matches'])
    ->middleware(['api.fixtures:player_match_history', 'api.quota:player_match_history'])
    ->name('api.public.players.matches');

Route::get('players/heroes', [PlayerController::class, 'heroes'])
    ->middleware(['api.fixtures:player_hero_all', 'api.quota:player_hero_all'])
    ->name('api.public.players.heroes');

Route::get('players/mmr', [PlayerController::class, 'mmr'])
    ->middleware(['api.fixtures:player_mmr', 'api.quota:player_mmr'])
    ->name('api.public.players.mmr');

Route::get('players/talents/build', [PlayerController::class, 'talentBuild'])
    ->middleware(['api.fixtures:player_talents_build', 'api.quota:player_talents_build'])
    ->name('api.public.players.talents.build');

/*
| Match reads. `replayID` is a path segment rather than a query parameter — it
| identifies the resource, and unlike a battletag it carries no characters that
| need encoding.
*/

Route::get('matches/{replayID}', [MatchController::class, 'show'])
    ->whereNumber('replayID')
    ->middleware(['api.fixtures:replay_data', 'api.quota:replay_data'])
    ->name('api.public.matches.show');

Route::get('matches/{replayID}/bans', [MatchController::class, 'bans'])
    ->whereNumber('replayID')
    ->middleware(['api.fixtures:replay_ban', 'api.quota:replay_ban'])
    ->name('api.public.matches.bans');

Route::get('replays/download', [MatchController::class, 'download'])
    ->middleware(['api.fixtures:replay_download', 'api.quota:replay_download'])
    ->name('api.public.replays.download');
