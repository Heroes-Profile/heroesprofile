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
    | Bypass global result cache (testing only)
    |--------------------------------------------------------------------------
    |
    | When true on local/develop, every global request skips cached results
    | and in-flight job dedup so a fresh async job runs each time.
    | Ignored on production (www).
    |
    */

    'bypass_cache' => env('GLOBAL_BYPASS_CACHE', false),

    /*
    |--------------------------------------------------------------------------
    | Batch query fan-out
    |--------------------------------------------------------------------------
    |
    | How many children of one batch query run at once — `heroes/talents/builds/all`
    | is ninety, one per hero. The Cloud Tasks queue permits 1000 concurrent
    | dispatches and 500 a second, so it applies no back pressure of its own and
    | this is the only ceiling there is.
    |
    | Raise it only as far as the worker service and the database connection limit
    | will take: these are minutes-long analytical queries, and the queue shares a
    | Cloud Run service with page traffic.
    |
    */

    'batch_max_in_flight' => (int) env('GLOBAL_BATCH_MAX_IN_FLIGHT', 10),

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

    /*
    |--------------------------------------------------------------------------
    | Cloud Run CPU (operational — not read by Laravel)
    |--------------------------------------------------------------------------
    |
    | When Cloud Tasks workers call the direct *.run.app handler URL, the
    | user-facing Cloud Run service (www) can use CPU throttling to reduce
    | idle billing on short poll requests:
    |
    |   gcloud run services update heroesprofile-website --region=us-east1 --cpu-throttling
    |
    | Keep --no-cpu-throttling on the worker path (handler URL / request timeout).
    |
    */

];
