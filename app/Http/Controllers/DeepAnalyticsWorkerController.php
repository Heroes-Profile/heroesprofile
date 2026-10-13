<?php

namespace App\Http\Controllers;

use App\Services\DeepAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Cloud Tasks fan-out for deep analytics requests: queues the deep parses for one chunk
 * of replays. Any failure returns an error so Cloud Tasks retries the chunk; replays
 * already parsed are cached by the parser, so a retry costs little.
 */
class DeepAnalyticsWorkerController extends Controller
{
    public function fanOut(Request $request, DeepAnalyticsService $deepAnalytics): JsonResponse
    {
        $validated = $request->validate([
            'replay_ids' => 'required|array|max:500',
            'replay_ids.*' => 'integer|min:1',
        ]);

        $queued = $deepAnalytics->fanOut(array_map('intval', $validated['replay_ids']));

        return response()->json(['queued' => $queued]);
    }
}
