<?php

namespace App\Http\Middleware;

use App\Services\ClientIpService;
use App\Services\WhitelistedIPsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiPostOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/v1/internal/*')) {
            return $next($request);
        }

        if (config('app.env') !== 'production') {
            return $next($request);
        }

        if (! $request->is('api/*') || ! $request->isMethod('POST')) {
            return $next($request);
        }

        $ip = ClientIpService::getClientIp($request);

        if (WhitelistedIPsService::isWhitelisted($ip)) {
            return $next($request);
        }

        if ($this->hasAllowedOrigin($request)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Forbidden.',
        ], 403);
    }

    protected function hasAllowedOrigin(Request $request): bool
    {
        $origin = $request->header('Origin');
        if ($origin && $this->matchesAllowedSite($origin)) {
            return true;
        }

        $referer = $request->header('Referer');
        if ($referer && $this->matchesAllowedSite($referer)) {
            return true;
        }

        return false;
    }

    /**
     * Exact origin comparison. A prefix match let look-alikes through:
     * `https://www.heroesprofile.com.evil.example` and `https://www.heroesprofile.com@evil.example`.
     */
    protected function matchesAllowedSite(string $value): bool
    {
        $origin = $this->originOf($value);

        return $origin !== null && in_array($origin, $this->allowedOrigins(), true);
    }

    /**
     * @return array<int, string>
     */
    protected function allowedOrigins(): array
    {
        $origins = config('cors.allowed_origins', []);
        $origins[] = (string) config('app.url');

        return array_values(array_unique(array_filter(array_map(
            fn (string $origin) => $this->originOf($origin),
            $origins
        ))));
    }

    /** `scheme://host[:port]` of an http(s) URL, or null for anything else. */
    private function originOf(string $url): ?string
    {
        $parts = parse_url($url);

        if ($parts === false
            || ! isset($parts['scheme'], $parts['host'])
            || isset($parts['user'])
            || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        return strtolower($parts['scheme']).'://'.strtolower($parts['host'])
            .(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
}
