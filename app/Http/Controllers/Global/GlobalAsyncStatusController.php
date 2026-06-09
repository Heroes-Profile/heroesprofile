<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Services\GlobalAsyncQueryService;
use Illuminate\Http\JsonResponse;

class GlobalAsyncStatusController extends Controller
{
    public function show(string $jobId, GlobalAsyncQueryService $globalAsyncQueryService): JsonResponse
    {
        return $globalAsyncQueryService->poll($jobId);
    }
}
