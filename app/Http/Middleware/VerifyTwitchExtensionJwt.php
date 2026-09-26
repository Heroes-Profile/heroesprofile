<?php

namespace App\Http\Middleware;

use App\Services\Twitch\TwitchJwt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requests from the extension frontend, authenticated by the JWT Twitch gave it.
 *
 * `Authorization: Extension <jwt>`. The verified claims are the only source of the
 * channel id; nothing in the query string or body is trusted for it.
 */
class VerifyTwitchExtensionJwt
{
    public const REQUEST_ATTRIBUTE = 'twitch_claims';

    public function __construct(private readonly TwitchJwt $jwt) {}

    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $header = (string) $request->header('Authorization', '');

        if (! str_starts_with($header, 'Extension ')) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $claims = $this->jwt->verify(substr($header, strlen('Extension ')));

        if ($claims === null) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        if ($role !== null && ($claims['role'] ?? null) !== $role) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $claims);

        return $next($request);
    }
}
