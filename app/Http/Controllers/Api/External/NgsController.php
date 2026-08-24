<?php

namespace App\Http\Controllers\Api\External;

use App\Auth\ApiKeyGuard;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SingleMatchController;
use App\Rules\HeroInputValidation;
use App\Rules\NgsReplayUrlValidation;
use App\Rules\NgsStatInputValidation;
use App\Services\Api\NgsHeroStatService;
use App\Services\Api\NgsLeaderboardService;
use App\Services\Api\NgsMatchService;
use App\Services\Api\NgsPlayerProfileService;
use App\Services\Api\NgsReplayIngestService;
use App\Support\GameLength;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
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

    /**
     * Ingests one NGS custom game.
     *
     * Parses synchronously and answers with the match and player links, because the
     * NGS tooling posts a game and expects somewhere to send people. The old handler
     * authenticated by interpolating the caller's token into SQL; that is now
     * `api.ngs:upload`, and `api_token` is no longer read from the request at all.
     */
    public function uploadGames(Request $request, NgsReplayIngestService $ingest): JsonResponse
    {
        $validated = $request->validate([
            // Lengths match `heroesprofile_logs.ngs_replays_sent`, the narrowest place
            // each value lands. That connection runs in strict mode, so anything
            // longer throws on insert rather than truncating — better a 422 here.
            'replay_url' => ['required', 'string', 'max:200', new NgsReplayUrlValidation],
            'mode' => ['required', 'string', 'in:prod,dev'],
            'season' => ['required', 'integer', 'min:1'],
            'round' => ['required', 'string', 'max:45'],
            'game' => ['required', 'string', 'max:45'],
            'team_one_name' => ['required', 'string', 'max:200'],
            'team_two_name' => ['required', 'string', 'max:200'],
            'team_one_player' => ['required', 'string', 'max:45'],
            'team_two_player' => ['required', 'string', 'max:45'],
            // Required rather than defaulted: the old handler let all four fall
            // through as null and then rejected the upload further down.
            'team_one_map_ban_1' => ['required', 'string', 'max:200'],
            'team_one_map_ban_2' => ['required', 'string', 'max:200'],
            'team_two_map_ban_1' => ['required', 'string', 'max:200'],
            'team_two_map_ban_2' => ['required', 'string', 'max:200'],
            'team_one_image_url' => ['sometimes', 'nullable', 'string', 'max:200'],
            'team_two_image_url' => ['sometimes', 'nullable', 'string', 'max:200'],
            // All three defaulted to 'NGS' on the old site.
            'tournament' => ['sometimes', 'string', 'max:45'],
            'team_one_division' => ['sometimes', 'string', 'max:45'],
            'team_two_division' => ['sometimes', 'string', 'max:45'],
        ]);

        $validated['tournament'] ??= 'NGS';
        $validated['team_one_division'] ??= 'NGS';
        $validated['team_two_division'] ??= 'NGS';
        $validated['api_key_reference'] = $this->keyReference($request);

        try {
            $payload = $ingest->ingest($validated, $this->connectionFor($validated['mode']));
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => ['code' => 'ngs_upload_failed', 'message' => $e->getMessage()],
            ], 422);
        }

        return response()->json($payload);
    }

    /** Removes a game and its players, talents, scores, bans and draft. */
    public function deleteGames(Request $request, NgsReplayIngestService $ingest): JsonResponse
    {
        $validated = $request->validate([
            'replayID' => ['required', 'integer', 'min:1'],
            'mode' => ['required', 'string', 'in:prod,dev'],
        ]);

        // There is no fixture for a delete — nothing sensible to hand back — so the
        // gate is explicit. Without it a test-mode caller would remove live rows,
        // which is the one thing test mode exists to prevent.
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context?->servesFixtures()) {
            return response()->json([
                'error' => [
                    'code' => 'test_mode',
                    'message' => 'This account is receiving test data and cannot delete NGS games.',
                ],
            ], 403);
        }

        $ingest->delete((int) $validated['replayID'], $this->connectionFor($validated['mode']));

        return response()->json(['deleted' => (int) $validated['replayID']]);
    }

    /** `dev` writes to the scratch copy of the NGS schema, as it did on the old site. */
    private function connectionFor(string $mode): string
    {
        return $mode === 'prod' ? 'heroesprofile_ngs' : 'heroesprofile_ngs_dev';
    }

    /**
     * Which key uploaded, for the log. Keys are stored hashed, so the plaintext the
     * old column held is not available to record.
     */
    private function keyReference(Request $request): string
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        return $context === null ? 'unknown' : 'key:'.$context->keyId;
    }
}
