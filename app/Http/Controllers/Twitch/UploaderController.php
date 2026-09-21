<?php

namespace App\Http\Controllers\Twitch;

use App\Http\Middleware\AuthenticateTwitchUploaderKey;
use App\Models\Api\TwitchChannel;
use App\Services\Twitch\TwitchEntitlementService;
use App\Services\Twitch\TwitchPushService;
use App\Services\Twitch\TwitchSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * What the desktop uploader talks to while a game is being played.
 *
 * Not the app's base Controller: that one builds GlobalDataService for every
 * request, and nothing here needs it until a snapshot is actually processed.
 */
class UploaderController extends Controller
{
    /** Checks a key from the uploader's settings screen. */
    public function whoami(Request $request, TwitchEntitlementService $entitlements): JsonResponse
    {
        $channel = $this->channel($request);

        return response()->json([
            'twitch_login' => $channel->twitch_login,
            'twitch_display_name' => $channel->twitch_display_name,
            'battletag' => $channel->battletag,
            'player_linked' => $channel->hasPlayerLinked(),
            'delay_seconds' => $channel->delay_seconds,
            'entitlement' => $entitlements->for($channel)->toArray(),
        ]);
    }

    /**
     * One full snapshot of the game so far.
     *
     * 402 when the channel has no access: the uploader stops sending and shows
     * why, and viewers are told once that the extension is off for this channel.
     */
    public function snapshot(
        Request $request,
        TwitchEntitlementService $entitlements,
        TwitchSnapshotService $snapshots,
        TwitchPushService $push,
    ): JsonResponse {
        $validated = $request->validate([
            'game_id' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
            'seq' => ['required', 'integer', 'min:0'],
            'phase' => ['required', 'in:lobby,in_game,ended'],
            'game_mode' => ['nullable', 'string', 'max:32'],
            'map' => ['nullable', 'string', 'max:64'],
            'game_version' => ['nullable', 'string', 'max:32'],
            'players' => ['required', 'array', 'min:1', 'max:10'],
            'players.*.name' => ['required', 'string', 'max:32'],
            'players.*.battletag' => ['required', 'integer', 'min:0'],
            'players.*.region' => ['required', 'integer', 'min:1', 'max:5'],
            'players.*.team' => ['required', 'integer', 'in:0,1'],
            'players.*.hero' => ['nullable', 'string', 'max:64'],
            'players.*.talents' => ['nullable', 'array', 'max:7'],
            'players.*.talents.*' => ['nullable', 'string', 'max:128'],
        ]);

        $channel = $this->channel($request);
        $entitlement = $entitlements->for($channel);

        if (! $entitlement->isActive()) {
            $push->publishInactive($channel);

            return response()->json([
                'active' => false,
                'reason' => $entitlement->source,
                'message' => 'The Twitch extension is not active for this channel. Any paid API plan includes it: heroesprofile.com/Api/Account',
            ], 402);
        }

        $push->clearInactive($channel);

        $result = $snapshots->ingest($channel, $validated);

        return response()->json(['active' => true] + $result);
    }

    private function channel(Request $request): TwitchChannel
    {
        return $request->attributes->get(AuthenticateTwitchUploaderKey::REQUEST_ATTRIBUTE);
    }
}
