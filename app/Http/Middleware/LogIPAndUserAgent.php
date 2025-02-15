<?php

namespace App\Http\Middleware;

use App\Models\BattlenetAccount;
use App\Models\IpLogging;
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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // $user = BattlenetAccount::find(1);
        // Auth::login($user);

        try {
            Cookie::queue(Cookie::forget('additional-battletags'));
            $ip = '';
            if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                    $addr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                    $ip = trim($addr[0]);
                } else {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

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
