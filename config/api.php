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

    /*
    | Signed-in accounts are held on /Api/Terms until they have accepted this
    | version. Bump it whenever the terms change materially.
    */

    'terms_version' => env('API_TERMS_VERSION', '2026-08-22'),

    /*
    | Whether accounts registered here start already on live data. Off until the
    | public API is in production: while the old site is still the one serving
    | customers, a new account has to go through the migration gate like any
    | other. Switch on at deploy, so accounts that only ever knew this site skip
    | an activation step that means nothing to them.
    |
    | Deliberately not the `users.migrated` column default — the old site shares
    | that table and its registration is still open, and an old-site signup marked
    | migrated would hold a working old token *and* draw live data here, which is
    | the one thing the gate exists to stop.
    */

    'new_accounts_migrated' => env('API_NEW_ACCOUNTS_MIGRATED', false),

    'rate_limits' => [
        'default' => 60,
        'developer' => 120,
        'anonymous' => 20,
    ],

];
