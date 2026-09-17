<?php

namespace App\Http\Controllers\Api\External;

use App\Auth\ApiKeyGuard;
use App\Http\Controllers\Controller;
use App\Rules\NgsReplayUrlValidation;
use App\Services\Api\NgsReplayIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * NGS ingestion. Restricted to accounts granted NGS upload access and carrying no
 * weekly quota — see RequireNgsAccess. The API serves no NGS or other esports data.
 */
class NgsController extends Controller
{
    /**
     * Shared with ValidateNgsUpload, which runs these before the fixtures gate.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function uploadRules(): array
    {
        return [
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
        ];
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
        $validated = $request->validate(self::uploadRules());

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
