<?php

namespace App\Http\Middleware;

use App\Models\BannedIPs;
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
            // Extract IP address using the same logic as LogIPAndUserAgent middleware
            $ip = $this->getClientIp($request);

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

    /**
     * Extract client IP address handling proxies
     */
    private function getClientIp(Request $request): string
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
}
