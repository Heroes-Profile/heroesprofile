<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Services\Api\RankSampleService;
use App\Services\ClientIpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Loading-screen samples from the uploader's opt-in rank reader, while it's being built - see
 * RankSampleService.
 *
 * Anonymous like the other uploader routes (a key bundled in a public repository would be no secret), so
 * the limits are the protection: a strict per-IP throttle on the route, and here at most `max_frames`
 * PNGs of `max_frame_kb` each plus a small JSON. Every answer carries `collect`; false tells the uploader
 * to stop sending.
 */
class RankSampleController extends Controller
{
    /** The first eight bytes of every PNG. */
    private const PNG_SIGNATURE = "\x89PNG\r\n\x1a\n";

    public function store(Request $request, RankSampleService $samples): JsonResponse
    {
        if (! $samples->collecting()) {
            return $this->answer(false, 'Not collecting samples', 200, false);
        }

        $limits = config('api.rank_samples');

        $raw = $request->input('meta');
        if (! is_string($raw) || $raw === '' || strlen($raw) > $limits['max_meta_kb'] * 1024) {
            return $this->answer(false, 'Missing or oversized meta', 422);
        }
        $meta = json_decode($raw, true);
        if (! is_array($meta)) {
            return $this->answer(false, 'Meta is not a JSON object', 422);
        }

        $frames = $request->file('frames');
        if (! is_array($frames) || $frames === [] || count($frames) > $limits['max_frames']) {
            return $this->answer(false, 'Expected 1 to '.$limits['max_frames'].' frames', 422);
        }
        foreach ($frames as $frame) {
            if (! $this->isPng($frame, $limits['max_frame_kb'] * 1024)) {
                return $this->answer(false, 'Frames must be PNG images of at most '.$limits['max_frame_kb'].' KB', 422);
            }
        }

        $samples->store($meta, $frames, ClientIpService::getClientIp($request));

        return $this->answer(true);
    }

    private function isPng(mixed $frame, int $maxBytes): bool
    {
        if (! $frame instanceof UploadedFile || ! $frame->isValid() || $frame->getSize() > $maxBytes) {
            return false;
        }
        $handle = fopen($frame->getRealPath(), 'rb');
        $head = $handle === false ? '' : (string) fread($handle, 8);
        if ($handle !== false) {
            fclose($handle);
        }

        return $head === self::PNG_SIGNATURE;
    }

    private function answer(bool $stored, ?string $error = null, int $status = 200, bool $collect = true): JsonResponse
    {
        return response()->json(array_filter([
            'stored' => $stored,
            'collect' => $collect,
            'error' => $error,
        ], fn ($value) => $value !== null), $status);
    }
}
