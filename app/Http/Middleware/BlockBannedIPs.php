<?php

namespace App\Http\Middleware;

use App\Models\BannedIPs;
use App\Services\ClientIpService;
use App\Services\WhitelistedIPsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockBannedIPs
{
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

            $ip = ClientIpService::getClientIp($request);

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
}
