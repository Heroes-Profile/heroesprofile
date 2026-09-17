<?php

namespace App\Services;

use App\Models\WhitelistIP;
use Illuminate\Database\Eloquent\Collection;

class WhitelistedIPsService
{
    /** Several middlewares ask on the same request; one query answers them all. */
    private static array $checked = [];

    /**
     * Check if an IP address is whitelisted
     */
    public static function isWhitelisted(string $ip): bool
    {
        if (array_key_exists($ip, self::$checked)) {
            return self::$checked[$ip];
        }

        try {
            return self::$checked[$ip] = WhitelistIP::isWhitelisted($ip);
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
