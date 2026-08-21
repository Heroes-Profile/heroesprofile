<?php

namespace App\Http\Controllers;

use App\Services\Api\ReplayUploadService;

class UploadController extends Controller
{
    /**
     * Replays sent from a browser are recorded under this source, and leaderboard
     * eligibility keys off it — so it stays `web`.
     */
    private const SOURCE = 'web';

    public function show()
    {
        return view('upload')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            // Built from config so it stays correct on either mount.
            'uploadUrl' => '/'.trim((string) config('api.path'), '/').'/upload/heroesprofile/'.self::SOURCE,
            // Same ceiling the endpoint enforces, so the page can reject a file
            // before spending a request on it.
            'maxBytes' => ReplayUploadService::MAX_BYTES,
        ]);
    }
}
