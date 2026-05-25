<?php

namespace App\Http\Middleware;

use App\Models\BattlenetAccount;
use App\Models\IpLogging;
use App\Services\ClientIpService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class LogIPAndUserAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // $user = BattlenetAccount::find(1);
        // Auth::login($user);

        try {
            Cookie::queue(Cookie::forget('additional-battletags'));
            $ip = ClientIpService::getClientIp($request);

            $page = substr($request->path(), 0, 500);
            $userAgent = $request->header('User-Agent');

            IpLogging::create([
                'ip' => $ip,
                'page' => $page,
                'user_agent' => $userAgent,
            ]);
        } catch (Exception $e) {
            // Handle any exceptions if necessary
        }

        return $next($request);
    }
}
