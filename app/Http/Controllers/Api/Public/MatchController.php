<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SingleMatchController;
use App\Services\Api\ReplayDownloadService;
use App\Services\Api\ReplayIndexService;
use App\Support\GameLength;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Match reads, delegating to the controller the site's own match page uses.
 *
 * SingleMatchController varies its output on Auth::check() for `replay_download_blocked`
 * and `patreon_subscriber`. The public API is stateless, so callers always get
 * the anonymous shape.
 *
 * Esports matches are out of scope here: CCL and MastersClash are being dropped,
 * and NGS has its own endpoints.
 */
class MatchController extends Controller
{
    /** Why a replay could not be served, in words a caller can act on. */
    private const DOWNLOAD_ERRORS = [
        'replay_deleted' => 'That replay is no longer stored.',
        'replay_not_found' => 'No replay found for that id.',
        'replay_unavailable' => 'That replay is not currently available for download.',
    ];

    public function show(Request $request, int $replayID): Response
    {
        $request->merge(['replayID' => $replayID]);

        $result = app()->call(
            [app(SingleMatchController::class), 'getData'],
            ['request' => $request]
        );

        if ($result instanceof Response) {
            return $result;
        }

        // The site formats length for display; every other endpoint reports it as
        // seconds. One field, one meaning.
        return response()->json(GameLength::inPayload($result));
    }

    /**
     * A page of replays, for building a local copy of the data.
     *
     * Replaces the old `/Replay/Min_id`, which the plan had classed as a hotsapi
     * artifact on the strength of a stale spec summary — the implementation
     * returned no hotsapi column and was a general bulk index.
     */
    public function index(Request $request, ReplayIndexService $replays): Response
    {
        $validated = $request->validate([
            'after' => ['sometimes', 'integer', 'min:0'],
            'timeframe_type' => ['sometimes', 'in:minor,major'],
            'timeframe' => ['sometimes', 'string', 'max:32'],
            'game_type' => ['sometimes', 'string', 'max:64'],
            'game_map' => ['sometimes', 'string', 'max:255'],
        ]);

        return response()->json($replays->page($validated));
    }

    /**
     * The original .StormReplay file, streamed as the old API did.
     */
    public function download(Request $request, ReplayDownloadService $replays): Response
    {
        $validated = $request->validate([
            'replayID' => ['required', 'integer'],
        ]);

        $result = $replays->download((int) $validated['replayID']);

        if ($result instanceof Response) {
            return $result;
        }

        return response()->json([
            'error' => [
                'code' => $result,
                'message' => self::DOWNLOAD_ERRORS[$result],
                'endpoint' => 'replay_download',
            ],
        ], $result === 'replay_not_found' ? 404 : 403);
    }

    /**
     * Bans alone, rather than making a caller pull the whole match to read them.
     * The site's match page gets these from the same service method.
     */
    public function bans(int $replayID): Response
    {
        return response()->json([
            'replayID' => $replayID,
            'bans' => $this->globalDataService->getReplayBans($replayID),
        ]);
    }
}
