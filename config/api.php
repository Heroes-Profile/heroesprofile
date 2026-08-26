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

    'path' => 'api/external/v1',

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

    'rate_limits' => [
        'default' => 60,
        'developer' => 120,
        'anonymous' => 20,

        /*
        | Per-minute floors for endpoints the plan-wide limit suits badly.
        |
        | The replay endpoints answer one record per call, so a caller works
        | through them in volume rather than a handful of requests at a time, and
        | each is a single indexed lookup rather than a minutes-long analytical
        | query. The old API gave these same endpoints exactly these ceilings.
        |
        | This governs how fast an allowance can be spent, never how much: the
        | weekly quota is still the only thing deciding volume. Without it the two
        | contradict each other — `replay_data` sold Partner a million calls a week
        | that sixty a minute cannot physically make in seven days.
        |
        | Floors, not overrides. A Developer key keeps its raised plan limit
        | wherever that is already the higher of the two.
        */

        'routes' => [
            // The three per-replay reads share a ceiling. They are asked for the
            // same way — one replay at a time, in volume, off the back of the
            // index — so a caller's pace should not depend on which slice of a
            // replay they happen to want.
            'api.external.replay.show' => 500,
            'api.external.replay.bans' => 500,
            'api.external.replay.draft' => 500,
            'api.external.replays.index' => 200,
        ],
    ],

];
