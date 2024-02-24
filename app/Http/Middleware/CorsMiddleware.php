<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
      // Add the appropriate headers to allow CORS from https://heroesprofile.com
      $response = $next($request);
        
      $response->headers->set('Access-Control-Allow-Origin', 'https://heroesprofile.com');
      $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
      $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

      return $response;
    }
}
