<?php

namespace App\Services;

use App\Models\BattletagNotAllowedDownloadReplay;
use App\Models\Replay;
use App\Models\ReplayFingerprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Replay downloads for signed-in site visitors.
 *
 * Separate from the API's `replays/download`, and deliberately so: this one is
 * authenticated by a Battlenet session rather than a key, and metered per person
 * per day rather than against a purchased allowance. The old site had both too —
 * the site's button called an anonymous endpoint on the API host that
 * re-authenticated the visitor from a battletag in the query string. Here the
 * session already says who they are.
 *
 * Esport replays are named by replay id in their own buckets. Everything else is
 * named by fingerprint in the main one.
 */
class SiteReplayDownloadService
{
    /** Per person, per UTC day. Matches what the old endpoint enforced. */
    public const DAILY_LIMIT = 100;

    private const EXTENSION = '.StormReplay';

    /** Which bucket an esport's replays live in. */
    private const ESPORT_DISKS = [
        'CCL' => 'gcs-ccl',
        'Other' => 'gcs-esport-other',
    ];

    public function __construct(private readonly GlobalDataService $globalDataService) {}

    /**
     * @return StreamedResponse|string the file, or a reason it cannot be served
     */
    public function download(int $replayID, string $battletag, ?int $userId, ?string $esport = null): StreamedResponse|string
    {
        if (BattletagNotAllowedDownloadReplay::where('battletag', $battletag)->exists()) {
            return 'download_blocked';
        }

        if ($this->downloadsToday($battletag) >= self::DAILY_LIMIT) {
            return 'daily_limit_reached';
        }

        $result = $esport !== null && isset(self::ESPORT_DISKS[$esport])
            ? $this->esportReplay($replayID, self::ESPORT_DISKS[$esport])
            : $this->standardReplay($replayID);

        if (is_string($result)) {
            return $result;
        }

        $this->record($replayID, $battletag, $userId);

        return $result;
    }

    private function esportReplay(int $replayID, string $disk): StreamedResponse|string
    {
        $path = $replayID.self::EXTENSION;

        return Storage::disk($disk)->exists($path)
            ? Storage::disk($disk)->download($path)
            : 'replay_unavailable';
    }

    private function standardReplay(int $replayID): StreamedResponse|string
    {
        // Availability is a rolling retention window, not a fixed replay id. The
        // old endpoint hardcoded 40981887, which went stale the day it was written.
        $dateAdded = Replay::where('replayID', $replayID)->value('date_added');

        if ($dateAdded === null) {
            return 'replay_not_found';
        }

        if (! $this->globalDataService->replayFileIsRetained($dateAdded)) {
            return 'replay_deleted';
        }

        $fingerprint = ReplayFingerprint::select('deleted', 'fingerprint')
            ->where('replayID', $replayID)
            ->first();

        if ($fingerprint === null) {
            return 'replay_not_found';
        }

        if ($fingerprint->deleted == 1) {
            return 'replay_deleted';
        }

        $path = $fingerprint->fingerprint.self::EXTENSION;

        return Storage::disk('gcs')->exists($path)
            ? Storage::disk('gcs')->download($path)
            : 'replay_unavailable';
    }

    private function downloadsToday(string $battletag): int
    {
        return DB::table('replay_downloads_not_api')
            ->where('battletag', $battletag)
            ->where('date_pulled', '>=', now('UTC')->startOfDay())
            ->count();
    }

    private function record(int $replayID, string $battletag, ?int $userId): void
    {
        DB::table('replay_downloads_not_api')->insert([
            'replayID' => $replayID,
            'user_id' => $userId,
            'battletag' => $battletag,
            'date_pulled' => now(),
        ]);
    }
}
