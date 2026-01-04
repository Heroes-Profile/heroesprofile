<?php

namespace App\Http\Middleware;

use App\Models\BannedIPs;
use App\Models\SuspiciousActivityLog;
use App\Services\WhitelistedIPsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class DetectScrapingPatterns
{
    /**
     * Maximum number of similar requests before flagging as scraping
     */
    protected $maxSimilarRequests = 10;

    /**
     * Time window in minutes to check for patterns
     */
    protected $timeWindowMinutes = 15;

    /**
     * Minimum number of unique pages with similar patterns
     */
    protected $minPatternMatches = 20;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use shared IP extraction method for consistency
        $ip = WhitelistedIPsService::getClientIp($request);

        // Check if IP is whitelisted - if so, bypass all scraping pattern detection
        if (WhitelistedIPsService::isWhitelisted($ip)) {
            return $next($request);
        }

        $userAgent = $request->header('User-Agent', '');
        $path = $request->path();

        // Check for suspicious/malformed user agent (but don't auto-ban based on this alone)
        if ($this->isSuspiciousUserAgent($userAgent)) {
            $this->logSuspiciousActivity($ip, $userAgent, $path, 'Suspicious user agent detected');
            // Note: We log but don't auto-ban on user agent alone - rely on behavioral patterns instead
        }

        // Check for scraping patterns
        if ($this->detectScrapingPattern($ip, $path)) {
            $this->logSuspiciousActivity($ip, $userAgent, $path, 'Scraping pattern detected');

            // Auto-ban disabled for testing - reviewing logs first
            // TODO: Enable auto-banning after reviewing logs and confirming detection accuracy
            /*
            if (!BannedIPs::isBanned($ip)) {
                BannedIPs::banIp($ip, 'Automated scraping pattern detected', $path);
            }
            return response()->json(['message' => 'Access denied. Automated scraping is not allowed.'], 403);
            */
        }

        return $next($request);
    }

    /**
     * Check if user agent is suspicious or malformed
     * Note: This is for logging purposes only - we rely on behavioral patterns for blocking
     */
    protected function isSuspiciousUserAgent(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        // Check for malformed patterns (not version numbers, which change over time)
        $suspiciousPatterns = [
            // Claims to be Mozilla but missing browser engine indicators
            function ($ua) {
                return stripos($ua, 'Mozilla/5.0') !== false &&
                       ! preg_match('/AppleWebKit|Gecko|Chrome|Safari|Firefox|Edge|Opera/i', $ua);
            },
            // Malformed version strings (e.g., "Version/." or "Version//")
            function ($ua) {
                return preg_match('/Version\/[^0-9]|Version\/\/|Chrome\/[^0-9]|Firefox\/[^0-9]/i', $ua);
            },
            // Suspiciously short or generic user agents
            function ($ua) {
                return strlen($ua) < 20 && stripos($ua, 'bot') === false;
            },
        ];

        foreach ($suspiciousPatterns as $check) {
            if ($check($userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect scraping patterns by analyzing recent requests from the same IP
     */
    protected function detectScrapingPattern(string $ip, string $currentPath): bool
    {
        // Use cache to track request patterns
        $cacheKey = "scraping_pattern_{$ip}";
        $patternData = Cache::get($cacheKey, []);

        // Extract URL pattern (e.g., "Global/Matchups/Talents/Anub'arak/" from "Global/Matchups/Talents/Anub'arak/Nazeebo")
        $urlPattern = $this->extractUrlPattern($currentPath);

        // Add current request to pattern data
        $patternData[] = [
            'path' => $currentPath,
            'pattern' => $urlPattern,
            'timestamp' => now()->timestamp,
        ];

        // Keep only requests within time window
        $cutoffTime = now()->subMinutes($this->timeWindowMinutes)->timestamp;
        $patternData = array_filter($patternData, function ($entry) use ($cutoffTime) {
            return $entry['timestamp'] > $cutoffTime;
        });

        // Group by URL pattern
        $patternGroups = [];
        foreach ($patternData as $entry) {
            $pattern = $entry['pattern'];
            if (! isset($patternGroups[$pattern])) {
                $patternGroups[$pattern] = [];
            }
            $patternGroups[$pattern][] = $entry;
        }

        // Check if any pattern has enough similar requests
        foreach ($patternGroups as $pattern => $entries) {
            if (count($entries) >= $this->minPatternMatches) {
                // Check if requests are sequential/patterned (not random)
                if ($this->isSequentialPattern($entries)) {
                    Cache::put($cacheKey, $patternData, now()->addMinutes($this->timeWindowMinutes));

                    return true;
                }
            }
        }

        // Store updated pattern data
        Cache::put($cacheKey, array_values($patternData), now()->addMinutes($this->timeWindowMinutes));

        return false;
    }

    /**
     * Extract URL pattern from path (removes the last segment)
     */
    protected function extractUrlPattern(string $path): string
    {
        $parts = explode('/', trim($path, '/'));
        if (count($parts) > 1) {
            // Remove last segment to get pattern
            array_pop($parts);

            return implode('/', $parts).'/';
        }

        return $path;
    }

    /**
     * Check if requests follow a sequential pattern (indicating automated scraping)
     */
    protected function isSequentialPattern(array $entries): bool
    {
        if (count($entries) < $this->minPatternMatches) {
            return false;
        }

        // Sort by timestamp
        usort($entries, function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        // Check timing regularity (requests should be roughly evenly spaced)
        $timeIntervals = [];
        for ($i = 1; $i < count($entries); $i++) {
            $interval = $entries[$i]['timestamp'] - $entries[$i - 1]['timestamp'];
            $timeIntervals[] = $interval;
        }

        // Calculate coefficient of variation (standard deviation / mean)
        // Low variation indicates regular/mechanical timing
        if (count($timeIntervals) > 0) {
            $mean = array_sum($timeIntervals) / count($timeIntervals);
            if ($mean > 0) {
                $variance = 0;
                foreach ($timeIntervals as $interval) {
                    $variance += pow($interval - $mean, 2);
                }
                $stdDev = sqrt($variance / count($timeIntervals));
                $coefficientOfVariation = $stdDev / $mean;

                // Low coefficient of variation (< 0.3) indicates very regular timing (bot-like)
                // High variation (> 0.5) indicates human-like irregular timing
                if ($coefficientOfVariation < 0.3 && $mean > 5) { // Regular intervals > 5 seconds
                    return true;
                }
            }
        }

        // Also check if paths follow alphabetical or numerical order (common scraping pattern)
        $paths = array_column($entries, 'path');
        if ($this->isAlphabeticalPattern($paths) || $this->isNumericalPattern($paths)) {
            return true;
        }

        return false;
    }

    /**
     * Check if paths follow alphabetical order (e.g., Anub'arak/Genji, Anub'arak/Greymane, etc.)
     */
    protected function isAlphabeticalPattern(array $paths): bool
    {
        if (count($paths) < 5) {
            return false;
        }

        // Extract last segment from each path
        $lastSegments = [];
        foreach ($paths as $path) {
            $parts = explode('/', trim($path, '/'));
            $lastSegments[] = end($parts);
        }

        // Check if segments are mostly in alphabetical order
        $sorted = $lastSegments;
        sort($sorted);

        // Count how many are in order
        $inOrder = 0;
        for ($i = 0; $i < count($lastSegments); $i++) {
            if ($lastSegments[$i] === $sorted[$i]) {
                $inOrder++;
            }
        }

        // If 70%+ are in alphabetical order, it's likely a scraping pattern
        return ($inOrder / count($lastSegments)) >= 0.7;
    }

    /**
     * Check if paths follow numerical order
     */
    protected function isNumericalPattern(array $paths): bool
    {
        if (count($paths) < 5) {
            return false;
        }

        // Extract numbers from paths
        $numbers = [];
        foreach ($paths as $path) {
            if (preg_match('/\d+/', $path, $matches)) {
                $numbers[] = (int) $matches[0];
            }
        }

        if (count($numbers) < 5) {
            return false;
        }

        // Check if numbers are sequential
        $sorted = $numbers;
        sort($sorted);
        $isSequential = true;
        for ($i = 1; $i < count($sorted); $i++) {
            if ($sorted[$i] - $sorted[$i - 1] !== 1) {
                $isSequential = false;
                break;
            }
        }

        return $isSequential;
    }

    /**
     * Log suspicious activity to database
     */
    protected function logSuspiciousActivity(string $ip, string $userAgent, string $path, string $reason): void
    {
        try {
            SuspiciousActivityLog::create([
                'ip' => $ip,
                'user_agent' => $userAgent,
                'path' => substr($path, 0, 500), // Ensure path doesn't exceed 500 chars
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            // Silently fail if database insert fails
        }
    }
}
