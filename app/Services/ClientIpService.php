<?php

namespace App\Services;

use Illuminate\Http\Request;

class ClientIpService
{
    /**
     * Extract client IP address.
     * Prefers Cloudflare's CF-Connecting-IP header (always set and can't be spoofed when
     * traffic flows through Cloudflare). Falls back to $request->ip() for non-Cloudflare
     * environments (local dev, direct access).
     */
    public static function getClientIp(Request $request): string
    {
        return $request->header('CF-Connecting-IP') ?? $request->ip();
    }
}
