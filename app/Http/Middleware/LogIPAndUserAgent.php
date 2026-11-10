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
            $ip = substr((string) ClientIpService::getClientIp($request), 0, 45);

            $page = substr($request->path(), 0, 500);
            $userAgent = $request->header('User-Agent');

            $header = fn (string $name, int $length) => $request->hasHeader($name)
                ? substr((string) $request->header($name), 0, $length)
                : null;

            IpLogging::create([
                'ip' => $ip,
                'page' => $page,
                'user_agent' => $userAgent,
                'sec_ch_ua' => $header('Sec-CH-UA', 255),
                'sec_ch_ua_mobile' => $header('Sec-CH-UA-Mobile', 10),
                'sec_ch_ua_platform' => $header('Sec-CH-UA-Platform', 50),
                'sec_fetch_site' => $header('Sec-Fetch-Site', 30),
                'sec_fetch_mode' => $header('Sec-Fetch-Mode', 30),
                'sec_fetch_dest' => $header('Sec-Fetch-Dest', 30),
                'accept_language' => $header('Accept-Language', 255),
                'accept_encoding' => $header('Accept-Encoding', 255),
                'referer' => $header('Referer', 500),
            ]);
        } catch (\Throwable $e) {
            // A failed log line shouldn't take the page down with it.
            report($e);
        }

        return $next($request);
    }
}
