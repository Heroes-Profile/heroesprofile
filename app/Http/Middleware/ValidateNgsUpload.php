<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\External\NgsController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates an NGS upload ahead of the fixtures gate, so test mode refuses the same
 * input live mode does.
 */
class ValidateNgsUpload
{
    public function handle(Request $request, Closure $next): Response
    {
        Validator::make($request->all(), NgsController::uploadRules())->validate();

        return $next($request);
    }
}
