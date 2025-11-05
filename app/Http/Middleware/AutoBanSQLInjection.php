<?php

namespace App\Http\Middleware;

use App\Models\BannedIPs;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AutoBanSQLInjection
{
    /**
     * SQL injection patterns to detect
     */
    protected $sqlInjectionPatterns = [
        '/(-1|0x)\s*(\'|"|\%27|\%22)?\s*(OR|AND)\s+\d+[\*\+\-\/]?\d*\s*[=<>]+\s*\d+/i', // -1' OR 5*5=25
        '/(\'|"|\%27|\%22)\s*(OR|AND)\s*(\'|"|\%27|\%22)/i', // ' OR '
        '/\bwaitfor\s+delay\b/i', // SQL Server time delay
        '/\bsleep\s*\(/i', // MySQL sleep
        '/\bpg_sleep\s*\(/i', // PostgreSQL sleep
        '/DBMS_PIPE\.RECEIVE_MESSAGE/i', // Oracle time delay
        '/\bif\s*\(\s*now\s*\(\s*\)\s*=\s*sysdate\s*\(\s*\)/i', // MySQL conditional
        '/\bXOR\s*\(/i', // XOR operations
        '/(\/\*|\*\/|--|\#)/i', // SQL comments
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $this->getClientIp($request);

        // Check URL path and route parameters for SQL injection
        $pathToCheck = $request->path();
        if ($request->route()) {
            foreach ($request->route()->parameters() as $value) {
                if (is_string($value)) {
                    $pathToCheck .= '/'.$value;
                }
            }
        }

        if ($this->containsSQLInjection($pathToCheck)) {
            $this->banAndBlock($ip, $request->fullUrl());

            return $this->blockedResponse();
        }

        return $next($request);
    }

    /**
     * Check if string contains SQL injection patterns
     */
    protected function containsSQLInjection(string $input): bool
    {
        $decodedInput = urldecode($input);

        foreach ($this->sqlInjectionPatterns as $pattern) {
            if (preg_match($pattern, $decodedInput)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ban the IP and log it
     */
    protected function banAndBlock(string $ip, string $url): void
    {
        if (! BannedIPs::isBanned($ip)) {
            BannedIPs::banIp($ip, 'SQL injection attempt detected');

            Log::warning('SQL Injection - IP Auto-Banned', [
                'ip' => $ip,
                'url' => $url,
            ]);
        }
    }

    /**
     * Return blocked response
     */
    protected function blockedResponse(): Response
    {
        return response()->json([
            'error' => 'Access denied',
        ], 403);
    }

    /**
     * Extract client IP address
     */
    protected function getClientIp(Request $request): string
    {
        if ($request->hasHeader('X-Forwarded-For')) {
            $forwardedFor = $request->header('X-Forwarded-For');
            if (strpos($forwardedFor, ',') !== false) {
                $ips = explode(',', $forwardedFor);

                return trim($ips[0]);
            }

            return $forwardedFor;
        }

        if ($request->hasHeader('X-Real-IP')) {
            return $request->header('X-Real-IP');
        }

        return $request->ip();
    }
}
