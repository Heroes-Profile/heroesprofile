<?php

namespace App\Http\Controllers;

use App\Services\SiteReplayDownloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * The download button on a match page.
 *
 * Replaces the old site's call out to `openApi/Replay/Download`, which took the
 * visitor's battletag and account id in the query string and re-checked them
 * against `battlenet_accounts` — because the API host had no session to read. On
 * this side the visitor is already signed in, so identity comes from the session
 * and cannot be supplied by the caller.
 *
 * Nothing to do with the public API's `replays/download`: that one is for API
 * customers, authenticated by key and charged against their weekly allowance.
 */
class ReplayDownloadController extends Controller
{
    /** Why a replay could not be served, in words a visitor can act on. */
    private const REASONS = [
        'download_blocked' => ['That account is not permitted to download replays.', 403],
        'daily_limit_reached' => ['You have reached the daily download limit. It resets at midnight UTC.', 429],
        'replay_deleted' => ['That replay is no longer stored. Replay files are kept for a limited time.', 410],
        'replay_not_found' => ['No replay found for that id.', 404],
        'replay_unavailable' => ['That replay is not currently available for download.', 404],
    ];

    public function __invoke(Request $request, int $replayID, SiteReplayDownloadService $replays): Response
    {
        $validated = $request->validate([
            'esport' => ['sometimes', 'nullable', 'in:CCL,Other'],
        ]);

        $user = Auth::user();

        // The old endpoint took these from the query string and validated the pair,
        // since it had no session. Reading them from the session instead means a
        // visitor cannot download as somebody else by editing a URL.
        $result = $replays->download(
            $replayID,
            (string) $user->battletag,
            $user->battlenet_accounts_id,
            $validated['esport'] ?? null,
        );

        if (! is_string($result)) {
            return $result;
        }

        [$message, $status] = self::REASONS[$result] ?? ['That replay could not be downloaded.', 404];

        return response()->view('errors.403', ['message' => $message], $status);
    }
}
