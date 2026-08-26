<?php

namespace App\Http\Controllers\Api\External;

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
use App\Services\GlobalDataService;
use App\Services\GlobalQueryService;
use App\Support\ApiParameters;
use App\Support\ApiSpecConfig;
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
 * Polling is free — quota is charged once, when the job is created.
 *
 * Inputs match the site's own globals validation: `timeframe_type`, `timeframe`
 * and `game_type` are required, everything else filters. `timeframe` is accepted
 * and `game_type` are accepted as one value or a comma-separated list, the old
 * API's single-value form included, and passed down as the arrays the
 * controllers expect.
 */
class GlobalStatsController extends Controller
{
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

        return $response->getStatusCode() === 202
            ? $this->describeJob($response, $jobId)
            : $response;
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
        if ($request->has('group_by_map') && ! ApiSpecConfig::declaresParameter($routeName, 'group_by_map')) {
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

        $target = $controller instanceof Controller ? $controller : app($controller);

        $result = app()->call([$target, $method], ['request' => $request]);

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
        $timeframes = (array) $request->input('timeframe', []);

        if ($timeframes === [] || $request->input('timeframe_type') === 'last_update') {
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
