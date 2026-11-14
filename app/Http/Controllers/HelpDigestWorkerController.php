<?php

namespace App\Http\Controllers;

use App\Services\HelpDigest\HelpDigestBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Cloud Tasks job, queued once a day from the VM: rebuilds the help chat's site digest.
 * A failed run leaves yesterday's digest in place.
 */
class HelpDigestWorkerController extends Controller
{
    public function build(HelpDigestBuilder $builder): JsonResponse
    {
        set_time_limit(0);

        $summary = $builder->build();
        Log::info('Help digest built', $summary);

        return response()->json($summary);
    }
}
