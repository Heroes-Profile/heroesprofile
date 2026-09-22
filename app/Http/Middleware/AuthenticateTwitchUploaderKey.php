<?php

namespace App\Http\Middleware;

use App\Models\Api\TwitchChannel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The uploader's key for the Twitch extension, in `X-HP-Twitch-Key`.
 *
 * Its own header rather than a Bearer token, so the API key guard never sees it and
 * never caches a miss for it. Only the hash is stored.
 */
class AuthenticateTwitchUploaderKey
{
    public const REQUEST_ATTRIBUTE = 'twitch_channel';

    public function handle(Request $request, Closure $next): Response
    {
        // Validation failures as JSON the uploader can show, never a redirect.
        $request->headers->set('Accept', 'application/json');

        $key = (string) $request->header('X-HP-Twitch-Key', '');
        $channel = $key !== '' ? TwitchChannel::findByUploaderKey($key) : null;

        if ($channel === null || $channel->user_id === null) {
            return response()->json([
                'error' => 'invalid_key',
                'message' => 'This uploader key is not valid. Create a new one at heroesprofile.com/Api/Account.',
            ], 401);
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $channel);

        return $next($request);
    }
}
