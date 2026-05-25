<?php

namespace App\Services;

use App\Models\RateLimitLog;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RateLimitLoggingService
{
    public function logFromThrottleException(Request $request, ThrottleRequestsException $exception): void
    {
        try {
            $replayId = $this->extractReplayId($request);
            $isOldReplay = null;

            if ($replayId !== null) {
                $isOldReplay = app(GlobalDataService::class)->isOldReplay($replayId);
            }

            RateLimitLog::create([
                'ip' => WhitelistedIPsService::getClientIp($request),
                'user_id' => Auth::id(),
                'http_method' => $request->method(),
                'path' => substr($request->path(), 0, 500),
                'query_string' => $request->getQueryString(),
                'limiter' => $this->resolveLimiter($request, $exception),
                'replay_id' => $replayId,
                'is_old_replay' => $isOldReplay,
                'user_agent' => $request->header('User-Agent'),
                'referer' => substr((string) $request->header('Referer'), 0, 500) ?: null,
                'retry_after' => $this->resolveRetryAfter($exception),
                'date_time' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if database insert fails
        }
    }

    protected function resolveLimiter(Request $request, ThrottleRequestsException $exception): string
    {
        $headers = $exception->getHeaders();
        $limit = isset($headers['X-RateLimit-Limit']) ? (int) $headers['X-RateLimit-Limit'] : null;

        return match ($limit) {
            15 => 'old-replay',
            180 => 'api',
            120 => 'global',
            3 => 'contact',
            default => $request->is('api/*') ? 'api' : 'global',
        };
    }

    protected function resolveRetryAfter(ThrottleRequestsException $exception): ?int
    {
        $headers = $exception->getHeaders();
        $retryAfter = $headers['Retry-After'] ?? null;

        return is_numeric($retryAfter) ? (int) $retryAfter : null;
    }

    protected function extractReplayId(Request $request): ?int
    {
        if ($request->path() === 'api/v1/match/single') {
            $replayId = $request->input('replayID');

            return is_numeric($replayId) ? (int) $replayId : null;
        }

        if (preg_match('#Match/Single/(\d+)#i', $request->path(), $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
