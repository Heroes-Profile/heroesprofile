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
     * Apply a stricter rate limit when loading archive replay pages (web routes only).
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

        if (! $this->isReplayWebPath($request->path())) {
            return $next($request);
        }

        $replayId = $this->extractReplayIdFromPath($request->path());

        if ($replayId === null || ! $this->globalDataService->isOldReplay($replayId)) {
            return $next($request);
        }

        return app(ThrottleRequests::class)->handle($request, $next, 'old-replay');
    }

    protected function isReplayWebPath(string $path): bool
    {
        return preg_match('#^(Esports/[^/]+/)?Match/Single/\d+#i', $path) === 1
            || preg_match('#^Esports/Other/[^/]+/Match/Single/\d+#i', $path) === 1;
    }

    protected function extractReplayIdFromPath(string $path): ?int
    {
        if (preg_match('#Match/Single/(\d+)#i', $path, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
