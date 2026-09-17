<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Api\External\Concerns\TranslatesInternalFailures;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Global\GlobalCompositionsController;
use App\Http\Controllers\Global\GlobalDraftController;
use App\Http\Controllers\Global\GlobalHeroMapStatsController;
use App\Http\Controllers\Global\GlobalHeroMatchupsTalentsController;
use App\Http\Controllers\Global\GlobalHeroMatchupStatsController;
use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Http\Controllers\Global\GlobalLeaderboardController;
use App\Http\Controllers\Global\GlobalPartyStatsController;
use App\Http\Controllers\Global\GlobalTalentBuilderController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
use App\Models\LeagueTier;
use App\Models\MatchPredictionSeason;
use App\Services\GlobalDataService;
use App\Services\GlobalQueryService;
use App\Support\ApiParameters;
use App\Support\ApiSpecConfig;
use App\Support\HeroLevelBands;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Global statistics. These are the only endpoints that can answer asynchronously.
 *
 * A cold query here can run for five minutes or more, so there is no waiting: a
 * cache hit returns 200 immediately, and a miss returns 202 with a job id for the
 * caller to poll. Holding the request open would burn a worker and still exceed
 * most clients' own timeouts.
 *
 * Polling is free. Quota is charged once, for the call that answered 200 or 202.
 *
 * Inputs match the site's own globals validation: `timeframe_type`, `timeframe`
 * and `game_type` are required, everything else filters. `timeframe` is accepted
 * and `game_type` are accepted as one value or a comma-separated list, the old
 * API's single-value form included, and passed down as the arrays the
 * controllers expect.
 */
class GlobalStatsController extends Controller
{
    use TranslatesInternalFailures;

    /** Suggested seconds between polls. A cold query is minutes, not seconds. */
    private const POLL_INTERVAL = 10;

    /** What the site's own composition pages send. Required by the controller. */
    private const DEFAULT_MINIMUM_GAMES = 100;

    public function heroStats(Request $request): Response
    {
        return $this->delegate($request, GlobalHeroStatsController::class, 'getGlobalHeroData');
    }

    public function heroMatchups(Request $request): Response
    {
        return $this->delegate($request, GlobalHeroMatchupStatsController::class, 'getHeroMatchupData', [], ['hero']);
    }

    public function talentDetails(Request $request): Response
    {
        return $this->delegate($request, GlobalTalentStatsController::class, 'getGlobalHeroTalentData', [], ['hero']);
    }

    /**
     * `total_builds` carries over from the old API — how many builds to return.
     * Defaults to what the site's own pages show.
     */
    public function talentBuilds(Request $request): Response
    {
        // Rejected here so a bad value is a 422 rather than being clamped silently.
        // The value itself is read off the request by the query.
        $request->validate([
            'total_builds' => ['sometimes', 'integer', 'min:1', 'max:'.Controller::MAX_BUILDS_TO_RETURN],
        ]);

        return $this->delegate($request, GlobalTalentStatsController::class, 'getGlobalHeroTalentBuildData', [
            // Required internally. `Popular` is what the site shows a visitor who
            // is not logged in — see GlobalDataService::getDefaultBuildType().
            'talentbuildtype' => 'Popular',
        ], ['hero']);
    }

    /** Which team compositions win, and how often. */
    public function compositions(Request $request): Response
    {
        return $this->delegate($request, GlobalCompositionsController::class, 'getCompositionsData', [
            'minimum_games' => self::DEFAULT_MINIMUM_GAMES,
        ]);
    }

    /** The heroes that make up one composition, identified by `composition_id`. */
    public function compositionHeroes(Request $request): Response
    {
        return $this->delegate($request, GlobalCompositionsController::class, 'getTopHeroData', [
            'minimum_games' => self::DEFAULT_MINIMUM_GAMES,
        ], ['composition_id']);
    }

    /** Draft order and pick position for one hero. */
    public function draft(Request $request): Response
    {
        return $this->delegate($request, GlobalDraftController::class, 'getDraftData', [], ['hero']);
    }

    /** How party size affects win rate. */
    public function party(Request $request): Response
    {
        return $this->delegate($request, GlobalPartyStatsController::class, 'getPartyStats');
    }

    /**
     * Season leaderboards.
     *
     * Does not use the shared globals rules — it is scoped by season rather than
     * by patch. `type` and `groupsize` default to what the page opens with, and
     * the season to the current one.
     */
    public function leaderboard(Request $request): Response
    {
        $type = $request->input('type', 'player');

        // The controller interpolates it into a cache key and a `where`, so a list
        // is a 500 there rather than a refusal.
        if ($request->filled('game_type') && (is_array($request->input('game_type')) || str_contains($request->input('game_type'), ','))) {
            return response()->json([
                'error' => ['code' => 'single_game_type_only', 'message' => 'This endpoint takes one game type.'],
            ], 422);
        }

        // Without these the board has nothing to rank by and answers empty.
        foreach (['hero', 'role'] as $subject) {
            if ($type === $subject && ! $request->filled($subject)) {
                return response()->json([
                    'error' => ['code' => 'missing_'.$subject, 'message' => 'A `'.$subject.'` board needs a '.$subject.'.'],
                ], 422);
            }
        }

        if ($type === 'match prediction') {
            // That board ranks prediction accuracy across everyone, so none of these
            // narrow it. Refused rather than silently ignored.
            foreach (['groupsize', 'region', 'tierrank', 'hero', 'role'] as $parameter) {
                if ($request->has($parameter)) {
                    return $this->unsupported($parameter, 'The match prediction board does not filter by it.');
                }
            }

            if ($request->filled('season')
                && ! MatchPredictionSeason::where('match_prediction_season_id', $request->input('season'))->exists()) {
                return response()->json([
                    'error' => ['code' => 'unknown_season', 'message' => 'Not a match prediction season.'],
                ], 422);
            }

            return $this->delegate($request, GlobalLeaderboardController::class, 'getLeaderboardData', [
                'season' => $this->globalDataService->getDefaultMatchPredictionSeason(),
                'game_type' => 'sl',
                // Required by the shared validation, read by nothing on this board.
                'groupsize' => 'Solo',
            ]);
        }

        // Alone among the global endpoints, this one validates `hero` by id. The
        // public contract is a name everywhere, so translate before delegating.
        if ($request->filled('hero')) {
            $heroId = $this->globalDataService->getHeroes()
                ->firstWhere('name', $request->input('hero'))?->id;

            if ($heroId === null) {
                return response()->json([
                    'error' => ['code' => 'unknown_hero', 'message' => 'No hero by that name.'],
                ], 422);
            }

            $request->merge(['hero' => $heroId]);
        }

        return $this->delegate(
            $request,
            GlobalLeaderboardController::class,
            'getLeaderboardData',
            [
                'season' => $this->globalDataService->getDefaultSeason(),
                'game_type' => 'sl',
                'type' => 'player',
                'groupsize' => 'Solo',
            ],
        );
    }

    /** One hero's win rate per map. */
    public function heroMaps(Request $request): Response
    {
        // Already one row per map; the controller never reads it.
        if ($request->has('game_map')) {
            return $this->unsupported('game_map', 'This endpoint already reports every map.');
        }

        return $this->delegate($request, GlobalHeroMapStatsController::class, 'getHeroStatMapData', [], ['hero']);
    }

    /**
     * Talent performance for one hero against or alongside another.
     *
     * `type` and `talent_view` default to what the site's own page opens with:
     * the hero's talents, measured against an enemy.
     */
    public function heroMatchupTalents(Request $request): Response
    {
        return $this->delegate($request, GlobalHeroMatchupsTalentsController::class, 'getHeroMatchupsTalentsData', [
            'type' => 'Enemy',
            'talent_view' => 'hero',
        ], ['hero', 'ally_enemy']);
    }

    /**
     * Every hero's builds in one call, under the same filters `heroes/talents/builds`
     * takes.
     *
     * Answers with a job id like the rest of this class, so the whole set costs one
     * call against the allowance no matter how long it takes to compute — polling
     * `/jobs/{id}` is free. The result is one entry per hero, not grouped by game
     * type: several game types are one query, not several answers.
     *
     * Every filter defaults to what the endpoint did before it took any, so a call
     * with no parameters still answers.
     */
    public function talentBuildsAll(Request $request): Response
    {
        $request->validate([
            'total_builds' => ['sometimes', 'integer', 'min:1', 'max:'.Controller::MAX_BUILDS_TO_RETURN],
        ]);

        return $this->delegate($request, GlobalTalentStatsController::class, 'getGlobalHeroTalentBuildDataAllFiltered', [
            'timeframe_type' => $this->globalDataService->getDefaultTimeframeType(),
            'timeframe' => [$this->globalDataService->getDefaultTimeframe()],
            'game_type' => ['qm', 'sl', 'ar'],
            'talentbuildtype' => 'Popular',
            'statfilter' => 'win_rate',
            'mirror' => '0',
        ]);
    }

    /**
     * Win rates for a partially chosen build, so a caller can evaluate a talent
     * before picking it. `selectedtalents` narrows to builds already containing
     * those talents.
     */
    public function talentBuilder(Request $request): Response
    {
        return $this->delegate($request, GlobalTalentBuilderController::class, 'getData', [], ['hero']);
    }

    /**
     * The replays behind a talent-builder result.
     *
     * `selectedtalents` is required here even though the controller treats it as
     * optional: with none selected it short-circuits and returns the same talent
     * list `heroes/talents/builder` does, so an omission would silently answer as
     * a different endpoint. Keyed by tier — `selectedtalents[1]`,
     * `selectedtalents[4]` and so on through 20 — with talent ids as values.
     */
    public function talentBuilderReplays(Request $request): Response
    {
        return $this->delegate($request, GlobalTalentBuilderController::class, 'getReplayData', [], ['hero', 'selectedtalents']);
    }

    /**
     * Results for a job returned by any of the above. Outside the quota middleware
     * on purpose: a five minute query polled every ten seconds would otherwise cost
     * thirty calls to deliver one result.
     */
    public function job(string $jobId, GlobalQueryService $queries): Response
    {
        $response = $queries->poll($jobId);

        // The service's own bodies are shaped for the site's poller, and a failed
        // job carries the exception text. Neither belongs in an API answer.
        return match ($response->getStatusCode()) {
            202 => $this->describeJob($response, $jobId),
            404 => response()->json([
                'error' => ['code' => 'job_not_found', 'message' => 'No job with that id. Jobs expire once collected or after they age out.'],
            ], 404),
            500 => response()->json([
                'error' => ['code' => 'job_failed', 'message' => 'The query behind this job failed. Make the original call again to start a new one.'],
            ], 500),
            default => $response,
        };
    }

    /**
     * @param  array<string, mixed>  $defaults  Parameters the site's own pages always
     *                                          send, so a public caller need not.
     * @param  array<int, string>  $requires  Parameters with no sensible default.
     *                                        Caught here because the controllers
     *                                        answer a miss with 200 and a `status`
     *                                        field rather than an error status.
     */
    /**
     * Rewrites `game_type` and `region` into what the internal controllers read,
     * or returns a 422 naming the values that were not recognised.
     *
     * Global controllers resolve regions through `getRegionStringToID()`, so they
     * want names — the opposite of the player endpoints, which want ids.
     */
    private function normalizeNames(Request $request): ?Response
    {
        foreach (['game_type', 'region'] as $parameter) {
            if (! $request->filled($parameter)) {
                continue;
            }

            $input = $request->input($parameter);

            [$resolved, $unknown] = $parameter === 'game_type'
                ? ApiParameters::gameTypes($input)
                : ApiParameters::regionNames($input);

            if ($unknown !== []) {
                return $this->unknownValues($parameter, $unknown);
            }

            // Values change, shape does not. Whether these arrive as a scalar or a
            // list is decided per endpoint by the config below, and the leaderboard
            // deliberately keeps them scalar — rewriting the shape here would
            // silently override that.
            $request->merge([
                $parameter => is_string($input) && ! str_contains($input, ',')
                    ? ($resolved[0] ?? $input)
                    : $resolved,
            ]);
        }

        return null;
    }

    /** @param  array<int, string>  $values */
    private function unknownValues(string $parameter, array $values): Response
    {
        return response()->json([
            'error' => [
                'code' => 'unknown_'.$parameter,
                'message' => 'Not a recognised '.str_replace('_', ' ', $parameter).': '.implode(', ', $values).'.',
                'accepted' => $parameter === 'game_type'
                    ? ApiParameters::GAME_TYPES
                    : ['NA', 'EU', 'KR', 'CN'],
            ],
        ], 422);
    }

    private function delegate(
        Request $request,
        Controller|string $controller,
        string $method,
        array $defaults = [],
        array $requires = [],
        // Which parameters arrive comma-separated. Null reads them from
        // `config/api_spec.php`, the same declaration the published specification
        // is built from — so a filter that becomes multi-select on the site is
        // described and split by one edit, and cannot be documented one way and
        // handled another. Pass an explicit list only to override that.
        ?array $arrays = null
    ): Response {
        $routeName = $request->route()?->getName();
        $arrays ??= ApiSpecConfig::multiForRoute($routeName);

        // `group_by_map` fans one request out into a query per playable map, so it
        // is offered only where that is worth doing. Refused rather than ignored
        // where it is not — the old API silently dropped it on some requests and
        // answered a different question than the one asked.
        // `false` asks for nothing, so only a request to group is refused.
        if ($request->boolean('group_by_map') && ! ApiSpecConfig::declaresParameter($routeName, 'group_by_map')) {
            return response()->json([
                'error' => [
                    'code' => 'group_by_map_unsupported',
                    'message' => 'This endpoint does not group by map.'
                        .' `heroes/maps` already reports one hero across every map,'
                        .' and `heroes/talents/builds/all` is a query per hero already —'
                        .' grouping that by map would be one call for every hero on every map.',
                ],
            ], 422);
        }

        // Past the support check above, and the batch rate limit has applied. The site's
        // own routes never set this, so they never fan out.
        if ($request->boolean('group_by_map')) {
            $request->attributes->set(GlobalQueryService::GROUP_BY_MAP_ALLOWED, true);
        }

        foreach ($requires as $parameter) {
            if (! $request->filled($parameter)) {
                return response()->json([
                    'error' => [
                        'code' => 'missing_'.$parameter,
                        'message' => 'This endpoint needs a '.str_replace('_', ' ', $parameter).'.',
                    ],
                ], 422);
            }
        }

        foreach ($defaults as $key => $value) {
            if (! $request->has($key)) {
                $request->merge([$key => $value]);
            }
        }

        if ($rejection = $this->rejectUnqueryableTimeframe($request)) {
            return $rejection;
        }

        // Names in, codes out. These controllers want short codes and region names;
        // the API takes either those or the display names, so `Storm League` and
        // `sl` both work, as do `NA` and `1`.
        if ($rejection = $this->normalizeNames($request)) {
            return $rejection;
        }

        // The rule objects take strings, but the controllers count() and whereIn()
        // these, so both have to arrive as arrays. Not universally, though — the
        // leaderboard interpolates game_type into a cache key, and declares its own
        // parameters rather than the globals set, so nothing here is marked multi.
        foreach ($arrays as $parameter) {
            if (is_string($request->input($parameter))) {
                $request->merge([
                    $parameter => explode(',', $request->input($parameter)),
                ]);
            }
        }

        if ($rejection = $this->rejectUnknownFilterValues($request)) {
            return $rejection;
        }

        $target = $controller instanceof Controller ? $controller : app($controller);

        $result = app()->call([$target, $method], ['request' => $request]);

        if ($failure = $this->internalFailure($result)) {
            return $failure;
        }

        if (! $result instanceof JsonResponse) {
            return response()->json($result);
        }

        if ($result->getStatusCode() !== 202) {
            return $result;
        }

        $payload = json_decode($result->getContent(), true);

        return $this->describeJob($result, $payload['job_id'] ?? null);
    }

    /**
     * Refuses a patch older than the site's own filters offer.
     *
     * The shared globals rules only check `valid_globals`, so older data passes
     * validation — the site simply never offers it in a dropdown. The API has no
     * dropdown, so without this a caller could query patches the site itself
     * considers not worth comparing against, and get answers nobody stands behind.
     *
     * `major` and `major_grouped` are prefixes rather than whole versions, so they
     * are judged by whether any queryable build starts with them.
     */
    private function rejectUnqueryableTimeframe(Request $request): ?Response
    {
        $input = $request->input('timeframe', []);

        // Runs before the list is split for the controllers, so a comma string is split
        // here. Checked whole, `a,b` matched no build and every multi-patch call failed.
        $timeframes = array_values(array_filter(
            array_map('trim', is_array($input) ? $input : explode(',', (string) $input)),
            fn ($timeframe) => $timeframe !== ''
        ));

        if ($timeframes === []) {
            return null;
        }

        $queryable = $this->globalDataService->queryableGameVersions();
        $exact = $request->input('timeframe_type', 'minor') === 'minor';

        foreach ($timeframes as $timeframe) {
            $ok = $exact
                ? in_array($timeframe, $queryable, true)
                : (bool) array_filter($queryable, fn ($v) => str_starts_with($v, trim((string) $timeframe)));

            if (! $ok) {
                return response()->json([
                    'error' => [
                        'code' => 'timeframe_unavailable',
                        'message' => 'That patch is not available for global statistics. The oldest queryable patch is '
                            .GlobalDataService::MINIMUM_GLOBALS_PATCH.'. The Variables section of the docs lists them all.',
                    ],
                ], 422);
            }
        }

        return null;
    }

    /**
     * The site's rules pass a list when any one entry is valid and drop the rest,
     * so `Alterac Pass,Alteracc Pass` would quietly answer for one map. Every entry
     * has to be recognised here. Map names are matched case-insensitively and
     * passed on as the site spells them, which its rules compare exactly.
     */
    private function rejectUnknownFilterValues(Request $request): ?Response
    {
        $checks = [
            'game_map' => null,
            'hero_level' => fn () => array_map('strval', array_keys(HeroLevelBands::all())),
            'league_tier' => fn () => LeagueTier::pluck('tier_id')->map(fn ($id) => (string) $id)->all(),
            'hero_league_tier' => fn () => LeagueTier::pluck('tier_id')->map(fn ($id) => (string) $id)->all(),
            'role_league_tier' => fn () => LeagueTier::pluck('tier_id')->map(fn ($id) => (string) $id)->all(),
        ];

        foreach ($checks as $parameter => $allowed) {
            if (! $request->filled($parameter)) {
                continue;
            }

            $input = $request->input($parameter);
            $values = array_map('trim', is_array($input) ? $input : explode(',', (string) $input));

            if ($parameter === 'game_map') {
                [$names, $unknown] = ApiParameters::playableMapNames($values);

                if ($unknown === []) {
                    $request->merge(['game_map' => is_array($input) ? $names : implode(',', $names)]);
                }
            } else {
                $unknown = array_values(array_diff($values, $allowed()));
            }

            if ($unknown !== []) {
                return response()->json([
                    'error' => [
                        'code' => 'unknown_'.$parameter,
                        'message' => 'Not a recognised '.str_replace('_', ' ', $parameter).': '.implode(', ', $unknown).'. The Variables section of the docs lists them.',
                    ],
                ], 422);
            }
        }

        return null;
    }

    private function unsupported(string $parameter, string $why): Response
    {
        return response()->json([
            'error' => ['code' => 'unsupported_parameter', 'message' => '`'.$parameter.'` is not accepted here. '.$why],
        ], 422);
    }

    /** Tells the caller where to collect the result and how often to ask. */
    private function describeJob(JsonResponse $response, ?string $jobId): Response
    {
        if ($jobId === null) {
            return $response;
        }

        return $response
            ->header('Retry-After', self::POLL_INTERVAL)
            ->header('Location', url('/v1/jobs/'.$jobId));
    }
}
