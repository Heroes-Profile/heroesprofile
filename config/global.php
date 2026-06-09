<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global async query processing (Cloud Tasks)
    |--------------------------------------------------------------------------
    |
    | When enabled, global stats API endpoints return 202 + job polling on
    | cache miss. A Cloud Task runs the heavy query via an internal HTTP
    | callback. Defaults to on in production.
    |
    */

    'async_enabled' => env('GLOBAL_ASYNC_ENABLED', env('APP_ENV') === 'production'),

    /*
    |--------------------------------------------------------------------------
    | Google Cloud Tasks
    |--------------------------------------------------------------------------
    */

    'cloud_tasks' => [
        'project_id' => env('CLOUD_TASKS_PROJECT_ID'),
        'location' => env('CLOUD_TASKS_LOCATION', 'us-east1'),
        'queue' => env('CLOUD_TASKS_QUEUE', 'global-queries'),
        'handler_url' => env('CLOUD_TASKS_HANDLER_URL'),
        'service_account' => env('CLOUD_TASKS_SERVICE_ACCOUNT'),
    ],

];
