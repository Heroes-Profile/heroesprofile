<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SingleMatchController;
use App\Models\Replay;
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
 * Esports matches are out of scope: the API serves no esports data.
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
        if ($refusal = $this->refuseUnviewable($replayID)) {
            return $refusal;
        }

        // A fresh request carrying only the id. The site controller also reads
        // `esport`, `tournament` and `user`, which switch it to the esports schemas
        // and unmask private accounts — none of that is the API's to offer.
        $internal = Request::create($request->path(), 'GET', ['replayID' => $replayID]);

        // The site page shows `Zemill`; an API caller needs `Zemill#1940`, since
        // that is what every player endpoint takes as input and the only form that
        // identifies someone uniquely. Private accounts are still nulled out
        // entirely, before this ever applies.
        $result = app()->call(
            [app(SingleMatchController::class)->withFullBattletags(), 'getData'],
            ['request' => $internal]
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
        if ($refusal = $this->refuseUnviewable($replayID)) {
            return $refusal;
        }

        return response()->json([
            'replayID' => $replayID,
            'bans' => $this->globalDataService->getReplayBans($replayID),
        ]);
    }

    /**
     * The draft in order — bans and picks together, as the match page shows it.
     *
     * Overlaps `bans` on purpose: this answers "how did the draft go", and a draft
     * with its bans removed is not one. A caller who only wants the bans asks for
     * the bans.
     */
    public function draft(int $replayID): Response
    {
        if ($refusal = $this->refuseUnviewable($replayID)) {
            return $refusal;
        }

        return response()->json([
            'replayID' => $replayID,
            'draft' => $this->globalDataService->getReplayDraftOrder($replayID),
        ]);
    }

    /**
     * Unknown replays and custom games. The API is stateless, so the site's
     * signed-in custom game opt-in never applies and custom games are never served.
     */
    private function refuseUnviewable(int $replayID): ?Response
    {
        $gameType = Replay::where('replayID', $replayID)->value('game_type');

        if ($gameType === null) {
            return $this->error('replay_not_found', self::DOWNLOAD_ERRORS['replay_not_found'], 404);
        }

        if ((int) $gameType === 0) {
            return $this->error('custom_match_unavailable', 'Custom games are not available through the API.', 403);
        }

        return null;
    }

    private function error(string $code, string $message, int $status): Response
    {
        return response()->json([
            'error' => ['code' => $code, 'message' => $message],
        ], $status);
    }
}
