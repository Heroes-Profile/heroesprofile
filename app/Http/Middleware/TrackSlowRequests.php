<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\LaravelIgnition\Facades\Flare;
use Symfony\Component\HttpFoundation\Response;

class TrackSlowRequests
{
    public const THRESHOLD_SECONDS = 120;

    /** Never sent to Flare. */
    public const HIDDEN_INPUT = ['api_token', 'password', 'password_confirmation', 'current_password'];

    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.env') === 'production' && ! $request->hasHeader('X-CloudTasks-TaskName')) {
            $request->attributes->set('_start_time', microtime(true));
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $start = $request->attributes->get('_start_time');

        if ($start === null) {
            return;
        }

        $duration = microtime(true) - $start;

        if ($duration < self::THRESHOLD_SECONDS) {
            return;
        }

        $seconds = round($duration, 2);
        $url = $request->fullUrlWithoutQuery(self::HIDDEN_INPUT);
        $method = $request->method();
        $route = optional($request->route())->getName() ?? $request->path();
        $status = $response->getStatusCode();

        Flare::context('url', $url);
        Flare::context('method', $method);
        Flare::context('route', $route);
        Flare::context('duration_seconds', $seconds);
        Flare::context('response_status', $status);
        Flare::context('input', $request->except(self::HIDDEN_INPUT));

        Flare::reportMessage(
            "Slow request ({$seconds}s): {$method} {$url}",
            'error'
        );
    }
}
