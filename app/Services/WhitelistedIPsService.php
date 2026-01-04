<?php

namespace App\Services;

use App\Models\WhitelistIP;
use Illuminate\Http\Request;

class WhitelistedIPsService
{
    /**
     * Extract client IP address handling proxies
     * This method is shared across all middleware to ensure consistent IP extraction
     */
    public static function getClientIp(Request $request): string
    {
        // Check for forwarded IP first (for proxy setups)
        if ($request->hasHeader('X-Forwarded-For')) {
            $forwardedFor = $request->header('X-Forwarded-For');
            if (strpos($forwardedFor, ',') !== false) {
                $ips = explode(',', $forwardedFor);

                return trim($ips[0]);
            }

            return $forwardedFor;
        }

        // Check for real IP header
        if ($request->hasHeader('X-Real-IP')) {
            return $request->header('X-Real-IP');
        }

        // Fall back to remote address
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
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getWhitelistedIPs()
    {
        return WhitelistIP::getAllWhitelistedIPs();
    }
}
