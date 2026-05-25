<?php

namespace App\Services;

use App\Models\WhitelistIP;
use Illuminate\Database\Eloquent\Collection;

class WhitelistedIPsService
{
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
