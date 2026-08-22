<?php

namespace App\Http\Controllers\Api\Public;

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
        $validated = $request->validate([
            'total_builds' => ['sometimes', 'integer', 'min:1', 'max:'.Controller::MAX_BUILDS_TO_RETURN],
        ]);

        $controller = app(GlobalTalentStatsController::class)
            ->setBuildsToReturn((int) ($validated['total_builds'] ?? Controller::DEFAULT_BUILDS_TO_RETURN));

        return $this->delegate($request, $controller, 'getGlobalHeroTalentBuildData', [
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
            arrays: [],
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
     * Every hero's most popular build in one call.
     *
     * Takes no parameters at all: the underlying controller reads the timeframe
     * and build type from the site's own defaults rather than the request.
     */
    public function talentBuildsAll(Request $request): Response
    {
        return $this->delegate($request, GlobalTalentStatsController::class, 'getGlobalHeroTalentBuildDataAll');
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
    private function delegate(
        Request $request,
        Controller|string $controller,
        string $method,
        array $defaults = [],
        array $requires = [],
        array $arrays = ['timeframe', 'game_type']
    ): Response {
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

        // The rule objects take strings, but the controllers count() and whereIn()
        // these, so both have to arrive as arrays. Not universally, though — see
        // the leaderboard, which interpolates game_type into a cache key.
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
