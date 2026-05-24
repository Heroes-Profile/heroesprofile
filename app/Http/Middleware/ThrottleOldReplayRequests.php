<?php

namespace App\Http\Middleware;

use App\Services\GlobalDataService;
use App\Services\WhitelistedIPsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottleOldReplayRequests
{
    public function __construct(
        protected GlobalDataService $globalDataService
    ) {}

    /**
     * Apply a stricter rate limit when accessing archive replays.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.env') !== 'production') {
            return $next($request);
        }

        $ip = WhitelistedIPsService::getClientIp($request);

        if (WhitelistedIPsService::isWhitelisted($ip)) {
            return $next($request);
        }

        $replayId = $this->extractReplayId($request);

        if ($replayId === null || ! $this->globalDataService->isOldReplay($replayId)) {
            return $next($request);
        }

        // Count the expensive data fetch once per replay, not the HTML shell.
        if ($request->path() !== 'api/v1/match/single') {
            return $next($request);
        }

        return app(ThrottleRequests::class)->handle($request, $next, 'old-replay');
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
