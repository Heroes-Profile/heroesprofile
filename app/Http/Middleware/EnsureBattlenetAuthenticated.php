<?php

namespace App\Http\Middleware;

use App\Models\BattlenetAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBattlenetAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        //$user = BattlenetAccount::find(1);
        //Auth::login($user);

        if (! Auth::check()) {
            return redirect('/Authenticate/Battlenet');
        }

        return $next($request);
    }
}
