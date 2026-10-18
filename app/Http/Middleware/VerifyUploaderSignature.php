<?php

namespace App\Http\Middleware;

use App\Services\Api\UploaderSignature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Only requests signed by a released build of the uploader get through - see UploaderSignature. */
class VerifyUploaderSignature
{
    public function __construct(private UploaderSignature $signature) {}

    public function handle(Request $request, Closure $next): Response
    {
        $problem = $this->signature->problem($request, $request->getContent());
        if ($problem !== null) {
            return response()->json(['stored' => false, 'error' => $problem], $this->signature->configured() ? 401 : 503);
        }

        return $next($request);
    }
}
