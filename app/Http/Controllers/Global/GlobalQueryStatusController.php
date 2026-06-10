<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Services\GlobalQueryService;
use Illuminate\Http\JsonResponse;

class GlobalQueryStatusController extends Controller
{
    public function show(string $jobId, GlobalQueryService $globalQueryService): JsonResponse
    {
        return $globalQueryService->poll($jobId);
    }
}
