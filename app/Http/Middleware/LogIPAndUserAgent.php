<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IpLogging;

class LogIPAndUserAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try{
          $ip = $request->ip();
          $page = $request->path();
          $userAgent = $request->header('User-Agent');

          IpLogging::create([
              'ip' => $ip,
              'page' => $page,
              'user_agent' => $userAgent,
          ]);
        } catch (Exception $e) {
        }


        return $next($request);
    }
}
