<?php

namespace App\Http\Controllers;

use App\Services\Api\ReplayUploadService;
use Illuminate\Http\Request;

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
            'uploadUrl' => $this->uploadUrl(self::SOURCE),
            // Same ceiling the endpoint enforces, so the page can reject a file
            // before spending a request on it.
            'maxBytes' => ReplayUploadService::MAX_BYTES,
        ]);
    }

    /**
     * The uploader other sites frame. `?source=` tags their uploads; the
     * uploaders' own sources are refused so a framed page can't claim them.
     */
    public function embed(Request $request)
    {
        $source = substr(preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $request->query('source'))), 0, 32);

        if ($source === '' || in_array($source, ReplayUploadService::PRIMARY_SOURCES, true)) {
            $source = self::SOURCE;
        }

        // Null outside the event. Opted-out visitors keep the event logo at its uncorrupted frame, as on the site.
        $event = $this->globalDataService->getXalatathEvent();
        $voidStage = $event === null ? null : ($request->cookie('void_corruption_optout') === '1' ? 0 : $event['stage']);

        return response()
            ->view('upload-embed', [
                'uploadUrl' => $this->uploadUrl($source),
                'maxBytes' => ReplayUploadService::MAX_BYTES,
                'voidStage' => $voidStage,
            ])
            ->header('Content-Security-Policy', 'frame-ancestors *');
    }

    /** How to embed it, for other sites. */
    public function widget()
    {
        return view('upload-widget')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }

    // Built from config so it stays correct on either mount.
    private function uploadUrl(string $source): string
    {
        return '/'.trim((string) config('api.path'), '/').'/upload/heroesprofile/'.$source;
    }
}
