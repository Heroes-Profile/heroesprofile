<?php

namespace App\Http\Middleware;

use App\Models\BannedIPs;
use App\Services\WhitelistedIPsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockBannedIPs
{
    /**
     * CIDR ranges belonging to cloud/datacenter providers commonly used by botnets.
     * These are checked in-memory before any database call.
     */
    private const BLOCKED_CIDR_RANGES = [
        // Tencent Cloud
        '43.128.0.0/11',
        '43.152.0.0/14',
        '43.160.0.0/11',
        '49.51.0.0/16',
        '101.32.0.0/15',
        '119.28.0.0/15',
        '124.156.0.0/16',
        '150.109.0.0/16',
        '170.106.0.0/16',
        // Alibaba Cloud
        '8.208.0.0/12',
        '47.74.0.0/15',
        '47.76.0.0/14',
        '47.88.0.0/14',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (config('app.env') !== 'production') {
                return $next($request);
            }

            $ip = WhitelistedIPsService::getClientIp($request);

            if ($this->isBlockedDatacenter($ip)) {
                return response('Access denied.', 403);
            }

            if (WhitelistedIPsService::isWhitelisted($ip)) {
                return $next($request);
            }

            if (BannedIPs::isBanned($ip)) {
                $banDetails = BannedIPs::getBanDetails($ip);

                return response()->view('errors.403', [
                    'message' => 'Access denied. Your IP address has been banned.',
                    'ban_reason' => $banDetails->reason ?? 'No reason provided',
                    'banned_date' => $banDetails->banned_date,
                ], 403);
            }

        } catch (\Exception $e) {
            // If there's an error checking bans, log it but don't block the request
        }

        return $next($request);
    }

    /**
     * Check if an IP falls within any blocked datacenter CIDR range.
     * Pure in-memory check -- no database or cache needed.
     */
    private function isBlockedDatacenter(string $ip): bool
    {
        $ipLong = ip2long($ip);

        if ($ipLong === false) {
            return false;
        }

        foreach (self::BLOCKED_CIDR_RANGES as $cidr) {
            [$subnet, $bits] = explode('/', $cidr);
            $subnetLong = ip2long($subnet);
            $mask = -1 << (32 - (int) $bits);

            if (($ipLong & $mask) === ($subnetLong & $mask)) {
                return true;
            }
        }

        return false;
    }
}
