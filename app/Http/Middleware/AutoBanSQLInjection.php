<?php

namespace App\Http\Middleware;

use App\Models\BannedIPs;
use App\Services\WhitelistedIPsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoBanSQLInjection
{
    /**
     * SQL injection patterns to detect
     */
    protected $sqlInjectionPatterns = [
        '/(-1|0x)\s*(\'|"|\%27|\%22)?\s*(OR|AND)\s+\d+[\*\+\-\/]?\d*\s*[=<>]+\s*\d+/i', // -1' OR 5*5=25
        '/(\'|"|\%27|\%22)\s*(OR|AND)\s*(\'|"|\%27|\%22)/i', // ' OR '
        '/\(\s*select\s+/i', // (select ... - Common in injection testing
        '/\bselect\s+.*\s+from\s+/i', // SELECT ... FROM (DUAL, tables, etc.)
        '/\b(union|insert|update|delete|drop|create|alter|exec|execute)\s+/i', // Other SQL keywords
        '/\bwaitfor\s+delay\b/i', // SQL Server time delay
        '/\bsleep\s*\(/i', // MySQL sleep
        '/\bpg_sleep\s*\(/i', // PostgreSQL sleep
        '/DBMS_PIPE\.RECEIVE_MESSAGE/i', // Oracle time delay
        '/\bif\s*\(\s*now\s*\(\s*\)\s*=\s*sysdate\s*\(\s*\)/i', // MySQL conditional
        '/\bXOR\s*\(/i', // XOR operations
        '/(\/\*|\*\/|--\s)/i', // SQL comments (-- followed by space, not just --)
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use shared IP extraction method for consistency
        $ip = WhitelistedIPsService::getClientIp($request);

        // Check if IP is whitelisted - if so, bypass all SQL injection checks
        if (WhitelistedIPsService::isWhitelisted($ip)) {
            return $next($request);
        }

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

        // Check headers for SQL injection (X-Forwarded-For, User-Agent, etc.)
        // Just block the request, don't ban (headers can be spoofed)
        $headersToCheck = ['X-Forwarded-For', 'User-Agent', 'Referer', 'X-Real-IP'];
        foreach ($headersToCheck as $header) {
            $value = $request->header($header);
            if ($value && $this->containsSQLInjection($value)) {
                return $this->blockedResponse();
            }
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
            BannedIPs::banIp($ip, 'SQL injection attempt detected', $url);
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
}
