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
        // Allowed hosts are derived from this: `s3.amazonaws.com/{bucket}` and
        // `{bucket}.s3.amazonaws.com`. See NgsReplayUrlValidation.
        'replay_bucket' => env('NGS_REPLAY_BUCKET', 'ngs-replay-storage'),

        'storage_disk' => 'gcs-ngs',
    ],

    /*
    | Signed-in accounts are held on /Api/Terms until they have accepted this
    | version. Bump it whenever the terms change materially.
    */

    'terms_version' => env('API_TERMS_VERSION', '2026-09-23'),

    /*
    | API keys keep working for accounts that have not accepted the current
    | version, or have not described their project, until this date (YYYY-MM-DD,
    | app timezone). The portal asks for both straight away. Unset means keys are
    | refused immediately.
    */

    'terms_enforce_from' => env('API_TERMS_ENFORCE_FROM'),

    /*
    | What serving an account actually costs, for the figures shown on their usage
    | page and in the admin console.
    |
    | Egress is only part of it. The replay download streams the file through the
    | app, so the container is held for as long as the client takes to receive it —
    | that instance time is billed too, and on a slow client it is the larger half.
    |
    | Rates are us-central1 list prices at the time of writing. They are env-backed
    | so a price change is a config edit rather than a deploy; check them against a
    | real bill rather than trusting they are still current.
    */

    'costs' => [
        'egress_per_gib' => (float) env('API_COST_EGRESS_PER_GIB', 0.085),
        'cpu_per_vcpu_second' => (float) env('API_COST_CPU_PER_VCPU_SECOND', 0.000024),
        'memory_per_gib_second' => (float) env('API_COST_MEMORY_PER_GIB_SECOND', 0.0000025),
        'per_million_requests' => (float) env('API_COST_PER_MILLION_REQUESTS', 0.40),

        /*
        | heroesprofile-website in us-east1: cpu 1000m, memory 2Gi. Billing is
        | request-based (`cpu-throttling: true`), so CPU is charged while requests
        | are in flight rather than for the instance's whole life — which is what
        | makes measuring per-request time worth anything at all.
        */
        'vcpus' => (float) env('API_COST_VCPUS', 1),
        'memory_gib' => (float) env('API_COST_MEMORY_GIB', 2),

        /*
        | Cloud Run bills the instance, not the request, and that service runs at a
        | containerConcurrency of 80 — so eighty requests sharing an instance for a
        | second cost one vCPU-second between them, not eighty. Adding up per-request
        | durations charges each of them the full second.
        |
        | 80 is the ceiling, not the observed figure; real concurrency is whatever
        | traffic happens to be. Dividing by it would understate as badly as dividing
        | by nothing overstates.
        |
        | Left at 1 deliberately: it divides by nothing, so the number is an upper
        | bound. Better to overstate a cost than to quietly understate one. Raise it
        | once there is a real bill to calibrate against — the true value is
        | somewhere in 1..80 and only the invoice knows where.
        */
        'assumed_concurrency' => (float) env('API_COST_ASSUMED_CONCURRENCY', 1),
    ],

    'rate_limits' => [
        'default' => 60,
        'developer' => 120,
        'anonymous' => 20,

        // Requests one key may have in flight at once, on every plan. See LimitApiConcurrency.
        'concurrent' => 5,

        /*
        | The pace a bulk download grant is allowed to run at, on the download route
        | and for `do_approved` accounts only.
        |
        | The weekly allowance is worthless if it cannot physically be spent: 150,000
        | a week at the Developer ceiling of 120 a minute is nearly 21 hours of
        | unbroken pulling. At 500 a minute a 100,000 backfill takes about three and
        | a half hours.
        |
        | This costs less than it looks like it should. Cloud Run is billed for
        | instance time, and the total is roughly downloads times how long each takes
        | — which does not change with how fast they are asked for. Raising this
        | concentrates the same spend into a shorter window rather than adding to it.
        | What it does raise is how many containers run at once, so weigh it against
        | the service's max instances and against the other traffic it would crowd.
        */
        'download_approved' => 500,

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

        /*
        | A ceiling, not a floor: it beats the plan limit rather than raising it,
        | Developer's 120 included.
        |
        | One of these requests is not one query. `group_by_map` is one per playable
        | map, and `heroes/talents/builds/all` is one per hero — so the number that
        | matters is this multiplied by `global.batch_max_in_flight`, which is how
        | many heavy queries a single key can have running at once.
        |
        | Collection is unaffected. Polling happens on `/jobs/{id}`, a different
        | route, so a batch already started is never slowed by this.
        |
        | Quota is untouched: a batch still costs one call however far it fans out.
        | If that gets abused, charging by fan-out is the lever — `EnforceApiQuota`
        | increments by one today and could increment by the child count instead.
        */

        'batch' => 1,

        /* Routes that fan out on every call, with or without a parameter saying so. */
        'batch_routes' => [
            'api.external.heroes.talents.builds.all',
        ],

        /*
        | The uploader's keyless routes, per IP. The ceilings their old routes had.
        | The fingerprint check is generous because the client makes one per replay
        | before deciding whether to upload at all.
        */

        'uploader' => [
            'upload_per_minute' => 60,
            'upload_per_day' => 20000,
            'fingerprints_per_minute' => 5000,
            'parsed_per_minute' => 60,
            'prematch_per_minute' => 120,
        ],
    ],

];
