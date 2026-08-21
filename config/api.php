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

    /*
    | NGS replay uploads name a file to fetch. Fetching an arbitrary caller-supplied
    | URL server-side is SSRF — on Cloud Run it reaches the metadata server and the
    | service account's token — so the source is pinned to the bucket NGS actually
    | publishes to.
    */

    'ngs' => [
        'replay_bucket' => env('NGS_REPLAY_BUCKET', 'ngs-replay-storage'),

        'replay_hosts' => [
            's3.amazonaws.com',
            'ngs-replay-storage.s3.amazonaws.com',
        ],

        'storage_disk' => 'gcs-ngs',
    ],

    'rate_limits' => [
        'default' => 60,
        'developer' => 120,
        'anonymous' => 20,
    ],

];
