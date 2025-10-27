<?php

namespace App\Http\Middleware;

use App\Models\BannedIPs;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedIPs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $this->getClientIP($request);

        
        // Check if IP is banned
        $bannedIP = BannedIPs::where('ip', $ip)->first();
        
        if ($bannedIP) {            
            // Return 403 Forbidden with a message
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Your IP address has been banned from accessing this service.',
                'reason' => $bannedIP->reason ?? 'No reason provided',
                'ip' => $ip
            ], 403);
        }
        return $next($request);
    }

    /**
     * Get the client's real IP address using the same method as IP logging
     *
     * @param Request $request
     * @return string
     */
    private function getClientIP(Request $request): string
    {
        $ip = '';
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $addr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip = trim($addr[0]);
            } else {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
}
