<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class GlobalDebugController extends Controller
{
    public function config(): JsonResponse
    {
        $host = request()->getHost();
        $debugAllowed = str_contains($host, 'develop')
            || in_array($host, ['localhost', '127.0.0.1'], true);

        if (! $debugAllowed && ! app()->environment(['local', 'development', 'staging'])) {
            abort(404);
        }

        $cloudTasks = config('global.cloud_tasks');
        $sampleTimeframe = [$this->globalDataService->getLatestPatch()];

        return response()->json([
            'app_env' => config('app.env'),
            'app_env_runtime' => app()->environment(),
            'app_env_getenv' => getenv('APP_ENV') !== false ? getenv('APP_ENV') : null,
            'queue_connection' => config('queue.default'),
            'cache_driver' => config('cache.default'),
            'global_async_enabled_runtime' => $this->globalDataService->isGlobalAsyncEnabled(),
            'global_async_enabled_config' => (bool) config('global.async_enabled'),
            'global_async_getenv' => getenv('GLOBAL_ASYNC_ENABLED') !== false ? getenv('GLOBAL_ASYNC_ENABLED') : null,
            'global_bypass_cache_runtime' => $this->globalDataService->shouldBypassGlobalCache(),
            'global_bypass_cache_config' => (bool) config('global.bypass_cache'),
            'global_bypass_cache_getenv' => getenv('GLOBAL_BYPASS_CACHE') !== false ? getenv('GLOBAL_BYPASS_CACHE') : null,
            'cache_fresh_seconds_sample' => $this->globalDataService->calculateCacheTimeInSeconds($sampleTimeframe),
            'config_cached' => app()->configurationIsCached(),
            'php_max_execution_time' => ini_get('max_execution_time'),
            'cloud_tasks' => [
                'project_id_set' => ! empty($cloudTasks['project_id']),
                'location' => $cloudTasks['location'],
                'queue' => $cloudTasks['queue'],
                'handler_url' => $cloudTasks['handler_url'],
                'service_account_set' => ! empty($cloudTasks['service_account']),
            ],
            'deploy_marker' => 'global-cloud-tasks-v1',
        ]);
    }
}
