<?php

use App\Http\Controllers\Api\Public\GlobalStatsController;
use App\Http\Controllers\Api\Public\MatchController;
use App\Http\Controllers\Api\Public\NgsController;
use App\Http\Controllers\Api\Public\PlayerController;
use App\Http\Controllers\Api\Public\ReferenceController;
use App\Http\Controllers\Api\Public\UploadController;
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

/*
| Global statistics. A cold query can run for minutes, so a cache miss answers
| 202 with a job id rather than holding the request open.
*/

Route::get('heroes/stats', [GlobalStatsController::class, 'heroStats'])
    ->middleware(['api.fixtures:heroes_stats', 'api.quota:heroes_stats'])
    ->name('api.public.heroes.stats');

Route::get('heroes/matchups', [GlobalStatsController::class, 'heroMatchups'])
    ->middleware(['api.fixtures:heroes_matchups', 'api.quota:heroes_matchups'])
    ->name('api.public.heroes.matchups');

Route::get('heroes/talents/details', [GlobalStatsController::class, 'talentDetails'])
    ->middleware(['api.fixtures:heroes_talents_details', 'api.quota:heroes_talents_details'])
    ->name('api.public.heroes.talents.details');

Route::get('heroes/talents/builds', [GlobalStatsController::class, 'talentBuilds'])
    ->middleware(['api.fixtures:heroes_talents_builds', 'api.quota:heroes_talents_builds'])
    ->name('api.public.heroes.talents.builds');

/*
| Job results. No quota middleware: polling is free, and the call that created
| the job has already been charged.
*/

Route::get('jobs/{jobId}', [GlobalStatsController::class, 'job'])
    ->name('api.public.jobs');

/*
| NGS. Granted access rather than a purchased tier: no quota, only the per-key
| throttle, and restricted to accounts holding the NGS flags.
*/

Route::get('ngs/match', [NgsController::class, 'match'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_match'])
    ->name('api.public.ngs.match');

Route::get('ngs/leaderboard/highest/average/stat', [NgsController::class, 'highestAverageStat'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_leaderboard_highest_average_stat'])
    ->name('api.public.ngs.leaderboard.average');

Route::get('ngs/leaderboard/highest/total/stat', [NgsController::class, 'highestTotalStat'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_leaderboard_highest_total_stat'])
    ->name('api.public.ngs.leaderboard.total');

Route::get('ngs/hero/stat', [NgsController::class, 'heroStat'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_hero_stat'])
    ->name('api.public.ngs.hero.stat');

Route::get('ngs/player/profile', [NgsController::class, 'playerProfile'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_player_profile'])
    ->name('api.public.ngs.player.profile');

Route::get('ngs/replay/data', [NgsController::class, 'replayData'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_replay_data'])
    ->name('api.public.ngs.replay.data');

/*
| Ingestion. Anonymous permanently, so no key, no fixtures, and no quota —
| there is no account to charge. Its own per-IP limits stand in, carried over
| from the old route; the shared per-key limiter skips this one, because a
| client working through a backlog would exhaust the anonymous 20 a minute in
| seconds.
*/

Route::post('upload/heroesprofile/{source}', [UploadController::class, 'store'])
    ->middleware(['throttle:upload', 'throttle:upload-daily'])
    ->name('api.public.upload');
