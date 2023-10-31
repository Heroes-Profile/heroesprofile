<?php

namespace App\Http\Middleware;

use App\Services\GlobalDataService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfPrivateProfilePage
{
    public function handle(Request $request, Closure $next): Response
    {
        $globalDataService = new GlobalDataService();

        $user = Auth::user();
        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $privateAccounts = $globalDataService->getPrivateAccounts();
        $containsAccount = $privateAccounts->contains(function ($account) use ($blizz_id, $region) {
            return $account['blizz_id'] == $blizz_id && $account['region'] == $region;
        });

        if ($containsAccount) {
            if (! Auth::check()) {
                return redirect('/');
            } elseif ($user->blizz_id != $blizz_id && $user->region != $region) {
                return redirect('/');
            }
        }

        return $next($request);

    }
}
