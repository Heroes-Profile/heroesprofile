<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SingleMatchController;
use App\Rules\HeroInputValidation;
use App\Rules\NgsStatInputValidation;
use App\Services\Api\NgsHeroStatService;
use App\Services\Api\NgsLeaderboardService;
use App\Services\Api\NgsMatchService;
use App\Services\Api\NgsPlayerProfileService;
use App\Support\GameLength;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * NGS endpoints. Restricted to accounts granted NGS access and carrying no
 * weekly quota — see RequireNgsAccess.
 *
 * Only the endpoints the old API exposed are reproduced. The registry holds keys
 * for several NGS pages that were never part of the API, and those stay unbuilt.
 */
class NgsController extends Controller
{
    public function match(Request $request, NgsMatchService $matches): JsonResponse
    {
        $validated = $request->validate([
            'season' => ['required', 'integer', 'min:1'],
            'division' => ['required', 'string', 'max:64'],
            'team' => ['required', 'string', 'max:128'],
            'round' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json($matches->forTeam(
            (int) $validated['season'],
            $validated['division'],
            $validated['team'],
            (int) $validated['round'],
        ));
    }

    public function heroStat(Request $request, NgsHeroStatService $heroStats): JsonResponse
    {
        $validated = $request->validate([
            'season' => ['required', 'integer', 'min:1'],
            'division' => ['required', 'string', 'max:64'],
            'hero' => ['required', new HeroInputValidation],
            'battletag' => ['sometimes', 'string', 'max:128'],
        ]);

        return response()->json($heroStats->forHero(
            (int) $validated['season'],
            $validated['division'],
            $validated['hero'],
            $validated['battletag'] ?? null,
        ));
    }

    /**
     * Full match detail from the NGS schema. Delegates to the controller behind
     * the site's own match pages, which already switches schema on `esport`.
     *
     * `replayID` stays a query parameter rather than a path segment, matching the
     * old API — unlike /v1/matches/{replayID}, which is a resource of its own.
     */
    public function replayData(Request $request): Response
    {
        $validated = $request->validate([
            'replayID' => ['required', 'integer'],
        ]);

        $request->merge([
            'replayID' => (int) $validated['replayID'],
            'esport' => 'NGS',
        ]);

        $result = app()->call(
            [app(SingleMatchController::class), 'getData'],
            ['request' => $request]
        );

        // Same conversion as `matches/{replayID}`: this shares that controller,
        // and so shared its display-formatted length.
        return $result instanceof Response ? $result : response()->json(GameLength::inPayload($result));
    }

    public function playerProfile(Request $request, NgsPlayerProfileService $profiles): JsonResponse
    {
        $validated = $request->validate([
            'battletag' => ['required', 'string', 'max:128'],
            'season' => ['sometimes', 'integer', 'min:1'],
            'division' => ['sometimes', 'string', 'max:64'],
        ]);

        return response()->json($profiles->forPlayer(
            $validated['battletag'],
            isset($validated['season']) ? (int) $validated['season'] : null,
            $validated['division'] ?? null,
        ));
    }

    public function highestAverageStat(Request $request, NgsLeaderboardService $leaderboards): JsonResponse
    {
        $validated = $this->validateLeaderboard($request);

        return response()->json([
            'stat' => $validated['stat'],
            'season' => isset($validated['season']) ? (int) $validated['season'] : null,
            'leaderboard' => $leaderboards->highestAverage(
                $validated['stat'],
                isset($validated['season']) ? (int) $validated['season'] : null
            ),
        ]);
    }

    public function highestTotalStat(Request $request, NgsLeaderboardService $leaderboards): JsonResponse
    {
        $validated = $this->validateLeaderboard($request);

        return response()->json([
            'stat' => $validated['stat'],
            'season' => isset($validated['season']) ? (int) $validated['season'] : null,
            'leaderboard' => $leaderboards->highestTotal(
                $validated['stat'],
                isset($validated['season']) ? (int) $validated['season'] : null
            ),
        ]);
    }

    /**
     * `stat` names a column, so it is checked against a fixed list rather than
     * being trusted — the old API concatenated it straight into the query.
     *
     * @return array<string, mixed>
     */
    private function validateLeaderboard(Request $request): array
    {
        return $request->validate([
            'stat' => ['required', new NgsStatInputValidation],
            'season' => ['sometimes', 'integer', 'min:1'],
        ]);
    }
}
