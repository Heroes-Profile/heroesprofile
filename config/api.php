<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public API
    |--------------------------------------------------------------------------
    |
    | `domain` is where the public API answers once DNS moves. The same routes are
    | always mounted under `path` as well, so they can be exercised before the
    | subdomain is repointed at this app.
    |
    */

    'domain' => env('API_PUBLIC_DOMAIN', 'api.heroesprofile.com'),

    'path' => 'api/public/v1',

    /*
    | Per-key request ceiling. The pricing page advertises 60 a minute, and 120 on
    | Developer. Unauthenticated callers fall back to an IP bucket.
    */

    'rate_limits' => [
        'default' => 60,
        'developer' => 120,
        'anonymous' => 20,
    ],

];
