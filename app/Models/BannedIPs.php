<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedIPs extends Model
{
    protected $table = 'banned_ips';

    protected $primaryKey = 'banned_ips_id';

    protected $connection = 'heroesprofile_logs';

    public $timestamps = false;

    protected $fillable = [
        'ip',
        'reason',
        'banned_date',
    ];

    protected $casts = [
        'banned_date' => 'datetime',
    ];

    /**
     * Check if an IP is banned
     */
    public static function isBanned($ip)
    {
        return self::where('ip', $ip)->exists();
    }

    /**
     * Get ban details for an IP
     */
    public static function getBanDetails($ip)
    {
        return self::where('ip', $ip)->first();
    }

    /**
     * Get all banned IPs
     */
    public static function getAllBannedIPs()
    {
        return self::orderBy('banned_date', 'desc')->get();
    }

    /**
     * Ban an IP address
     */
    public static function banIp($ip, $reason = null)
    {
        return self::create([
            'ip' => $ip,
            'reason' => $reason,
            'banned_date' => now(),
        ]);
    }

    /**
     * Unban an IP address
     */
    public static function unbanIp($ip)
    {
        return self::where('ip', $ip)->delete();
    }
}
