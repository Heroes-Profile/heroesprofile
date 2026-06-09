<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Services\GlobalQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalQueryWorkerController extends Controller
{
    public function process(Request $request, GlobalQueryService $globalQueryService): JsonResponse
    {
        $jobId = $request->input('job_id');

        if (! is_string($jobId) || $jobId === '') {
            return response()->json(['error' => 'Missing job_id'], 400);
        }

        $globalQueryService->runJob($jobId);

        return response()->json(['ok' => true, 'job_id' => $jobId]);
    }
}
