<?php

use App\Http\Controllers\Api\External\GlobalStatsController;
use App\Http\Controllers\Api\External\MatchController;
use App\Http\Controllers\Api\External\NgsController;
use App\Http\Controllers\Api\External\PlayerController;
use App\Http\Controllers\Api\External\PreMatchController;
use App\Http\Controllers\Api\External\ReferenceController;
use App\Http\Controllers\Api\External\ToolsController;
use App\Http\Controllers\Api\External\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| External API v1
|--------------------------------------------------------------------------
|
| Mounted twice by RouteServiceProvider: on the API subdomain, and under
| `api/external/v1` so it is reachable before DNS moves.
|
| Every endpoint carries both keyed middlewares. `api.fixtures` serves example
| data to accounts that have not activated live data; `api.quota` charges the
| ones that have. `php artisan api:check-fixtures` fails if the two get out of
| step or a fixture file is missing.
|
*/

Route::get('maps', [ReferenceController::class, 'maps'])
    ->middleware(['api.fixtures:maps', 'api.quota:maps'])
    ->name('api.external.maps');

Route::get('heroes', [ReferenceController::class, 'heroes'])
    ->middleware(['api.fixtures:heroes', 'api.quota:heroes'])
    ->name('api.external.heroes');

Route::get('heroes/talents', [ReferenceController::class, 'talents'])
    ->middleware(['api.fixtures:heroes_talents', 'api.quota:heroes_talents'])
    ->name('api.external.heroes.talents');

Route::get('patches', [ReferenceController::class, 'patches'])
    ->middleware(['api.fixtures:patches', 'api.quota:patches'])
    ->name('api.external.patches');

Route::get('mmr/tier', [ReferenceController::class, 'mmrTier'])
    ->middleware(['api.fixtures:mmr_tier', 'api.quota:mmr_tier'])
    ->name('api.external.mmr.tier');

/*
| Player endpoints. Identified by `battletag` and `region` in the query string,
| the same pair the old API took; blizz_id is resolved server-side.
*/

Route::get('players', [PlayerController::class, 'profile'])
    ->middleware(['api.fixtures:player', 'api.quota:player'])
    ->name('api.external.players');

Route::get('players/matches', [PlayerController::class, 'matches'])
    ->middleware(['api.fixtures:player_match_history', 'api.quota:player_match_history'])
    ->name('api.external.players.matches');

Route::get('players/heroes', [PlayerController::class, 'heroes'])
    ->middleware(['api.fixtures:player_hero_all', 'api.quota:player_hero_all'])
    ->name('api.external.players.heroes');

Route::get('players/heroes/single', [PlayerController::class, 'hero'])
    ->middleware(['api.fixtures:player_hero_single', 'api.quota:player_hero_single'])
    ->name('api.external.players.heroes.single');

Route::get('players/maps', [PlayerController::class, 'maps'])
    ->middleware(['api.fixtures:player_map_all', 'api.quota:player_map_all'])
    ->name('api.external.players.maps');

Route::get('players/maps/single', [PlayerController::class, 'map'])
    ->middleware(['api.fixtures:player_map_single', 'api.quota:player_map_single'])
    ->name('api.external.players.maps.single');

Route::get('players/roles', [PlayerController::class, 'roles'])
    ->middleware(['api.fixtures:player_role_all', 'api.quota:player_role_all'])
    ->name('api.external.players.roles');

Route::get('players/roles/single', [PlayerController::class, 'role'])
    ->middleware(['api.fixtures:player_role_single', 'api.quota:player_role_single'])
    ->name('api.external.players.roles.single');

Route::get('players/mmr', [PlayerController::class, 'mmr'])
    ->middleware(['api.fixtures:player_mmr', 'api.quota:player_mmr'])
    ->name('api.external.players.mmr');

Route::get('players/mmr/heroes', [PlayerController::class, 'heroMmr'])
    ->middleware(['api.fixtures:player_mmr_hero', 'api.quota:player_mmr_hero'])
    ->name('api.external.players.mmr.heroes');

Route::get('players/mmr/roles', [PlayerController::class, 'roleMmr'])
    ->middleware(['api.fixtures:player_mmr_role', 'api.quota:player_mmr_role'])
    ->name('api.external.players.mmr.roles');

Route::get('players/talents/build', [PlayerController::class, 'talentBuild'])
    ->middleware(['api.fixtures:player_talents_build', 'api.quota:player_talents_build'])
    ->name('api.external.players.talents.build');

Route::get('players/matchups', [PlayerController::class, 'matchups'])
    ->middleware(['api.fixtures:player_matchups', 'api.quota:player_matchups'])
    ->name('api.external.players.matchups');

Route::get('players/friendfoe', [PlayerController::class, 'friendFoe'])
    ->middleware(['api.fixtures:player_friendfoe', 'api.quota:player_friendfoe'])
    ->name('api.external.players.friendfoe');

/*
| Match reads. `replayID` is a path segment rather than a query parameter — it
| identifies the resource, and unlike a battletag it carries no characters that
| need encoding.
*/

Route::get('replays', [MatchController::class, 'index'])
    ->middleware(['api.fixtures:replay_index', 'api.quota:replay_index'])
    ->name('api.external.replays.index');

Route::get('matches/{replayID}', [MatchController::class, 'show'])
    ->whereNumber('replayID')
    ->middleware(['api.fixtures:replay_data', 'api.quota:replay_data'])
    ->name('api.external.matches.show');

Route::get('matches/{replayID}/bans', [MatchController::class, 'bans'])
    ->whereNumber('replayID')
    ->middleware(['api.fixtures:replay_ban', 'api.quota:replay_ban'])
    ->name('api.external.matches.bans');

Route::get('replays/download', [MatchController::class, 'download'])
    ->middleware(['api.fixtures:replay_download', 'api.quota:replay_download'])
    ->name('api.external.replays.download');

/*
| Global statistics. A cold query can run for minutes, so a cache miss answers
| 202 with a job id rather than holding the request open.
*/

Route::get('heroes/stats', [GlobalStatsController::class, 'heroStats'])
    ->middleware(['api.fixtures:heroes_stats', 'api.quota:heroes_stats'])
    ->name('api.external.heroes.stats');

Route::get('heroes/matchups', [GlobalStatsController::class, 'heroMatchups'])
    ->middleware(['api.fixtures:heroes_matchups', 'api.quota:heroes_matchups'])
    ->name('api.external.heroes.matchups');

Route::get('heroes/talents/details', [GlobalStatsController::class, 'talentDetails'])
    ->middleware(['api.fixtures:heroes_talents_details', 'api.quota:heroes_talents_details'])
    ->name('api.external.heroes.talents.details');

Route::get('heroes/talents/builds', [GlobalStatsController::class, 'talentBuilds'])
    ->middleware(['api.fixtures:heroes_talents_builds', 'api.quota:heroes_talents_builds'])
    ->name('api.external.heroes.talents.builds');

Route::get('compositions', [GlobalStatsController::class, 'compositions'])
    ->middleware(['api.fixtures:global_compositions', 'api.quota:global_compositions'])
    ->name('api.external.compositions');

Route::get('compositions/heroes', [GlobalStatsController::class, 'compositionHeroes'])
    ->middleware(['api.fixtures:global_compositions_heroes', 'api.quota:global_compositions_heroes'])
    ->name('api.external.compositions.heroes');

Route::get('draft', [GlobalStatsController::class, 'draft'])
    ->middleware(['api.fixtures:global_draft', 'api.quota:global_draft'])
    ->name('api.external.draft');

Route::get('party', [GlobalStatsController::class, 'party'])
    ->middleware(['api.fixtures:global_party', 'api.quota:global_party'])
    ->name('api.external.party');

Route::get('heroes/maps', [GlobalStatsController::class, 'heroMaps'])
    ->middleware(['api.fixtures:heroes_map_stats', 'api.quota:heroes_map_stats'])
    ->name('api.external.heroes.maps');

Route::get('heroes/matchups/talents', [GlobalStatsController::class, 'heroMatchupTalents'])
    ->middleware(['api.fixtures:heroes_matchups_talents', 'api.quota:heroes_matchups_talents'])
    ->name('api.external.heroes.matchups.talents');

Route::get('heroes/talents/builds/all', [GlobalStatsController::class, 'talentBuildsAll'])
    ->middleware(['api.fixtures:heroes_talents_builds_all', 'api.quota:heroes_talents_builds_all'])
    ->name('api.external.heroes.talents.builds.all');

Route::get('heroes/talents/builder', [GlobalStatsController::class, 'talentBuilder'])
    ->middleware(['api.fixtures:talent_builder', 'api.quota:talent_builder'])
    ->name('api.external.heroes.talents.builder');

Route::get('heroes/talents/builder/replays', [GlobalStatsController::class, 'talentBuilderReplays'])
    ->middleware(['api.fixtures:talent_builder_replays', 'api.quota:talent_builder_replays'])
    ->name('api.external.heroes.talents.builder.replays');

Route::get('leaderboard', [GlobalStatsController::class, 'leaderboard'])
    ->middleware(['api.fixtures:leaderboard', 'api.quota:leaderboard'])
    ->name('api.external.leaderboard');

/*
| Tools. Scoped to neither a patch nor a player, so no globals parameters.
*/

Route::get('tools/randomize', [ToolsController::class, 'randomize'])
    ->middleware(['api.fixtures:randomize_me', 'api.quota:randomize_me'])
    ->name('api.external.tools.randomize');

Route::get('tools/activity/players/unique', [ToolsController::class, 'uniquePlayers'])
    ->middleware(['api.fixtures:activity_unique_players', 'api.quota:activity_unique_players'])
    ->name('api.external.tools.activity.players.unique');

/*
| Job results. No quota middleware: polling is free, and the call that created
| the job has already been charged.
*/

Route::get('jobs/{jobId}', [GlobalStatsController::class, 'job'])
    ->name('api.external.jobs');

/*
| NGS. Granted access rather than a purchased tier: no quota, only the per-key
| throttle, and restricted to accounts holding the NGS flags.
*/

Route::get('ngs/match', [NgsController::class, 'match'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_match'])
    ->name('api.external.ngs.match');

Route::get('ngs/leaderboard/highest/average/stat', [NgsController::class, 'highestAverageStat'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_leaderboard_highest_average_stat'])
    ->name('api.external.ngs.leaderboard.average');

Route::get('ngs/leaderboard/highest/total/stat', [NgsController::class, 'highestTotalStat'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_leaderboard_highest_total_stat'])
    ->name('api.external.ngs.leaderboard.total');

Route::get('ngs/hero/stat', [NgsController::class, 'heroStat'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_hero_stat'])
    ->name('api.external.ngs.hero.stat');

Route::get('ngs/player/profile', [NgsController::class, 'playerProfile'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_player_profile'])
    ->name('api.external.ngs.player.profile');

Route::get('ngs/replay/data', [NgsController::class, 'replayData'])
    ->middleware(['api.ngs', 'api.fixtures:ngs_replay_data'])
    ->name('api.external.ngs.replay.data');

/*
| Ingestion. Anonymous permanently, so no key, no fixtures, and no quota —
| there is no account to charge. Its own per-IP limits stand in, carried over
| from the old route; the shared per-key limiter skips this one, because a
| client working through a backlog would exhaust the anonymous 20 a minute in
| seconds.
*/

Route::post('upload/heroesprofile/{source}', [UploadController::class, 'store'])
    ->middleware(['throttle:upload', 'throttle:upload-daily'])
    ->name('api.external.upload');

/*
| The uploader's other keyless calls, each carrying the ceiling its old route
| had. `replays/parsed` and `prematch` answer plain text rather than JSON: the
| client string-compares the first and Int32.TryParses the second, so an
| envelope on either would kill the feature silently. Do not "fix" them.
*/

Route::get('replays/fingerprints/{fingerprint}', [UploadController::class, 'fingerprint'])
    ->middleware('throttle:replay-fingerprints')
    ->name('api.external.replays.fingerprint');

Route::get('replays/parsed', [UploadController::class, 'parsed'])
    ->middleware('throttle:replay-parsed')
    ->name('api.external.replays.parsed');

Route::post('prematch', [PreMatchController::class, 'store'])
    ->middleware('throttle:prematch')
    ->name('api.external.prematch');
