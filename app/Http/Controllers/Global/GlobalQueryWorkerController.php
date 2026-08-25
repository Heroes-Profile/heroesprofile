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

        // Cloud Tasks counts retries from zero. A batch reports a child as failed
        // only once the queue has spent its attempts, so it needs to know which
        // one this is.
        $attempt = ((int) $request->header('X-CloudTasks-TaskRetryCount', '0')) + 1;

        $globalQueryService->runJob($jobId, $attempt);

        return response()->json(['ok' => true, 'job_id' => $jobId]);
    }
}
