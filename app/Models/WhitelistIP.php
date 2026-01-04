<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhitelistIP extends Model
{
    protected $table = 'whitelist_ips';

    protected $primaryKey = 'whitelist_ips_id';

    protected $connection = 'heroesprofile_logs';

    public $timestamps = false;

    protected $fillable = [
        'ip',
        'reason',
    ];

    /**
     * Check if an IP is whitelisted
     */
    public static function isWhitelisted($ip)
    {
        return self::where('ip', $ip)->exists();
    }

    /**
     * Get whitelist details for an IP
     */
    public static function getWhitelistDetails($ip)
    {
        return self::where('ip', $ip)->first();
    }

    /**
     * Get all whitelisted IPs
     */
    public static function getAllWhitelistedIPs()
    {
        return self::orderBy('whitelist_ips_id', 'desc')->get();
    }

    /**
     * Add an IP to whitelist
     */
    public static function addIp($ip, $reason = null)
    {
        return self::create([
            'ip' => $ip,
            'reason' => $reason,
        ]);
    }

    /**
     * Remove an IP from whitelist
     */
    public static function removeIp($ip)
    {
        return self::where('ip', $ip)->delete();
    }
}
