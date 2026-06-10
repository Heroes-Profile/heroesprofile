<?php

namespace App\Http\Middleware;

use Closure;
use Google\Auth\AccessToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCloudTasksRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasHeader('X-CloudTasks-TaskName')) {
            abort(403, 'Forbidden');
        }

        $authHeader = $request->header('Authorization', '');
        if (! str_starts_with($authHeader, 'Bearer ')) {
            abort(403, 'Forbidden');
        }

        $token = substr($authHeader, 7);
        $audience = config('global.cloud_tasks.handler_url');

        if (! $audience) {
            abort(503, 'Cloud Tasks handler URL not configured.');
        }

        $accessToken = new AccessToken;
        $payload = $accessToken->verify($token, ['audience' => $audience]);

        if ($payload === false) {
            abort(403, 'Invalid task token.');
        }

        return $next($request);
    }
}
