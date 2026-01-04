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
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Extract IP address using shared method for consistency
            $ip = WhitelistedIPsService::getClientIp($request);

            // Check if IP is whitelisted - if so, bypass all blocks
            if (WhitelistedIPsService::isWhitelisted($ip)) {
                return $next($request);
            }

            // Check if IP is banned
            if (BannedIPs::isBanned($ip)) {
                $banDetails = BannedIPs::getBanDetails($ip);

                // Return 403 Forbidden response
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
}
