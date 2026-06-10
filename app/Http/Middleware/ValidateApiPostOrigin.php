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

    protected function matchesAllowedSite(string $value): bool
    {
        foreach ($this->allowedOriginPrefixes() as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    protected function allowedOriginPrefixes(): array
    {
        $origins = config('cors.allowed_origins', []);

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '') {
            $origins[] = $appUrl;
        }

        return array_values(array_unique(array_map(
            fn (string $origin) => rtrim($origin, '/'),
            $origins
        )));
    }
}
