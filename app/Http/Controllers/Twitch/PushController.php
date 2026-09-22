<?php

namespace App\Http\Controllers\Twitch;

use App\Services\Twitch\TwitchPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Runs a delayed update when its Cloud Task fires. Behind `cloud.tasks`, so only
 * our own queue's service account can reach it.
 */
class PushController extends Controller
{
    public function push(Request $request, TwitchPushService $push): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['required', 'string', 'max:32'],
            'game_id' => ['required', 'string', 'max:64'],
            'seq' => ['required', 'integer', 'min:0'],
            'payload' => ['required', 'string', 'max:'.(int) config('twitch.max_payload_bytes')],
        ]);

        $sent = $push->publish($validated['channel_id'], $validated['game_id'], (int) $validated['seq'], $validated['payload']);

        // Always 200: a skipped or failed update is not worth a retry, because the
        // next one carries the whole game again.
        return response()->json(['sent' => $sent]);
    }
}
