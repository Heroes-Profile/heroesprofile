<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;

/**
 * The guide for consumers of the old API.
 *
 * Prose rather than generated: the endpoint list is on the docs page, and what a
 * porting consumer needs is what changed and why, which nothing can infer.
 */
class MigratingController extends Controller
{
    public function index()
    {
        return view('api.migrating', [
            'authenticated' => Auth::guard('api_web')->check(),
            'domain' => config('api.domain'),
            'minimumPatch' => GlobalDataService::MINIMUM_GLOBALS_PATCH,
        ]);
    }
}
