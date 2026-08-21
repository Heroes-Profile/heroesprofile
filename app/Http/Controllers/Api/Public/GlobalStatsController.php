<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Global\GlobalHeroMatchupStatsController;
use App\Http\Controllers\Global\GlobalHeroStatsController;
use App\Http\Controllers\Global\GlobalTalentStatsController;
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

    public function heroStats(Request $request): Response
    {
        return $this->delegate($request, GlobalHeroStatsController::class, 'getGlobalHeroData');
    }

    public function heroMatchups(Request $request): Response
    {
        return $this->delegate($request, GlobalHeroMatchupStatsController::class, 'getHeroMatchupData');
    }

    public function talentDetails(Request $request): Response
    {
        return $this->delegate($request, GlobalTalentStatsController::class, 'getGlobalHeroTalentData');
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

        return $this->delegate($request, $controller, 'getGlobalHeroTalentBuildData');
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

    private function delegate(Request $request, Controller|string $controller, string $method): Response
    {
        // The rule objects take strings, but the controllers count() and whereIn()
        // these, so both have to arrive as arrays.
        foreach (['timeframe', 'game_type'] as $parameter) {
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
