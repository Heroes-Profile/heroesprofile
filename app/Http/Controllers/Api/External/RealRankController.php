<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;
use App\Services\Api\RealRankService;
use App\Services\ClientIpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Real Storm League ranks from the uploader's opt-in rank reader, for a replay already on the site - see
 * RealRankService. Only a released uploader's signed request reaches here (VerifyUploaderSignature), within
 * a per-IP throttle.
 */
class RealRankController extends Controller
{
    public function store(Request $request, RealRankService $ranks): JsonResponse
    {
        $replayID = $request->json('replayID');
        $sent = $request->json('ranks');
        if (! is_int($replayID) || $replayID <= 0 || ! is_array($sent) || $sent === [] || count($sent) > config('api.ranks.max_ranks')) {
            return response()->json(['stored' => false, 'error' => 'Expected a replayID and 1 to '.config('api.ranks.max_ranks').' ranks'], 422);
        }

        $version = $request->header('X-HP-Uploader-Version');
        $result = $ranks->store($replayID, array_values(array_filter($sent, 'is_array')), ClientIpService::getClientIp($request), is_string($version) ? $version : null);
        if ($result === null) {
            return response()->json(['stored' => false, 'error' => 'No Storm League players known for this replay'], 404);
        }

        return response()->json(['stored' => $result['stored'] > 0] + $result);
    }
}
