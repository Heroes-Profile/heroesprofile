<?php

namespace App\Services\Api;

use App\Models\Replay;
use App\Models\ReplayFingerprint;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves the original .StormReplay file for a match.
 *
 * Streamed rather than handed over as a signed URL, matching the old API byte for
 * byte so existing clients need no change. Replays are 2-5 MB, so proxying them
 * through the app costs little.
 *
 * The file is named by fingerprint, not replay id.
 */
class ReplayDownloadService
{
    private const DISK = 'gcs';

    private const EXTENSION = '.StormReplay';

    public function __construct(private readonly GlobalDataService $globalDataService) {}

    /**
     * @return StreamedResponse|string the streamed file, or a failure reason
     */
    public function download(int $replayID): StreamedResponse|string
    {
        // Files are purged on a rolling window, so age decides availability. The
        // old API hardcoded a replay id as the cutoff, which went stale the day
        // after it was written.
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

        if (! Storage::disk(self::DISK)->exists($path)) {
            return 'replay_unavailable';
        }

        return Storage::disk(self::DISK)->download($path);
    }
}
