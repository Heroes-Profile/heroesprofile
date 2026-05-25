<?php

namespace App\Services;

use App\Models\WhitelistIP;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class WhitelistedIPsService
{
    /**
     * Extract client IP address handling proxies.
     * Relies on TrustProxies middleware so $request->ip() reflects the visitor IP on Cloud Run.
     */
    public static function getClientIp(Request $request): string
    {
        return $request->ip();
    }

    /**
     * Check if an IP address is whitelisted
     */
    public static function isWhitelisted(string $ip): bool
    {
        try {
            return WhitelistIP::isWhitelisted($ip);
        } catch (\Exception $e) {
            // If there's an error checking whitelist, return false to be safe
            return false;
        }
    }

    /**
     * Get all whitelisted IP addresses
     *
     * @return Collection
     */
    public static function getWhitelistedIPs()
    {
        return WhitelistIP::getAllWhitelistedIPs();
    }
}
