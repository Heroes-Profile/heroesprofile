<?php

namespace App\Http\Controllers\Twitch;

use App\Http\Middleware\VerifyTwitchExtensionJwt;
use App\Models\Api\TwitchChannel;
use App\Services\Twitch\TwitchEntitlementService;
use App\Services\Twitch\TwitchSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * The extension's config view, seen only by the broadcaster.
 *
 * The only request the extension ever makes to us, and only one person makes it.
 * Viewers get everything from Twitch.
 */
class BroadcasterController extends Controller
{
    public function status(Request $request, TwitchEntitlementService $entitlements, TwitchSnapshotService $snapshots): JsonResponse
    {
        $claims = $request->attributes->get(VerifyTwitchExtensionJwt::REQUEST_ATTRIBUTE);

        $channel = TwitchChannel::where('twitch_user_id', (string) $claims['channel_id'])->first();

        if ($channel === null || $channel->user_id === null) {
            return response()->json(['linked' => false]);
        }

        $latest = $snapshots->latest($channel);

        return response()->json([
            'linked' => true,
            'twitch_login' => $channel->twitch_login,
            'battletag' => $channel->battletag,
            'player_linked' => $channel->hasPlayerLinked(),
            'has_key' => $channel->uploader_key_hash !== null,
            'uploader_last_seen_at' => $channel->uploader_last_seen_at?->toIso8601String(),
            'delay_seconds' => $channel->delay_seconds,
            'entitlement' => $entitlements->for($channel)->toArray(),
            // Undelayed: the broadcaster already knows what is in their own game.
            'latest' => $latest ? json_decode($latest['payload'], true) : null,
        ]);
    }
}
