<?php

namespace App\Http\Middleware;

use App\Models\Api\ApiRequestLogging;
use App\Providers\RouteServiceProvider;
use App\Services\ClientIpService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * One row per keyed API call: who, what they sent, and what they got back.
 *
 * Written on terminate so the status is known. Parameters are captured on the way
 * in, before controllers get a chance to rewrite them.
 */
class LogApiRequest
{
    private const PARAMETERS_ATTRIBUTE = 'api_log.parameters';

    public function handle(Request $request, Closure $next): Response
    {
        $parameters = $request->input();
        unset($parameters['api_token']);

        $request->attributes->set(self::PARAMETERS_ATTRIBUTE, $parameters);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->attributes->has(self::PARAMETERS_ATTRIBUTE)
            || $request->routeIs(...RouteServiceProvider::UPLOADER_ROUTES)) {
            return;
        }

        try {
            $parameters = $request->attributes->get(self::PARAMETERS_ATTRIBUTE);

            ApiRequestLogging::create([
                'api_account_id' => Auth::guard('api_key')->user()?->id,
                'ip' => substr((string) ClientIpService::getClientIp($request), 0, 45),
                'method' => $request->method(),
                'page' => substr($request->path(), 0, 500),
                'parameters' => $parameters === [] ? null : json_encode($parameters),
                'status' => $response->getStatusCode(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        } catch (\Throwable $e) {
            // A failed log line shouldn't take the call down with it.
            report($e);
        }
    }
}
