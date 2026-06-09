<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global async query processing
    |--------------------------------------------------------------------------
    |
    | When enabled, global stats API endpoints return 202 + job polling on cache
    | miss and use afterResponse() background processing. Defaults to on in
    | production only. Set GLOBAL_ASYNC_ENABLED=true locally to test the flow.
    |
    */

    'async_enabled' => env('GLOBAL_ASYNC_ENABLED', env('APP_ENV') === 'production'),

];
