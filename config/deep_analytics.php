<?php

return [

    // The replay parser's deep analytics service (Cloud Run replay-parser-deep). Tasks are
    // signed by the global.cloud_tasks service account with this URL as the audience.
    'service_url' => env('DEEP_ANALYTICS_SERVICE_URL', 'https://replay-parser-deep-484440392946.us-east1.run.app'),

    // Cloud Tasks queue for the per-replay deep parses; it caps how many run at once.
    'queue' => env('DEEP_ANALYTICS_QUEUE', 'deep-analytics'),

    // This app's own fan-out route. A request queues one task here per chunk of replays,
    // and each of those queues the chunk's deep parses, so the page answers right away.
    'fanout_url' => env('DEEP_ANALYTICS_FANOUT_URL'),
    'fanout_chunk' => 50,

    // Where replays are read from: archive bundles, or the upload bucket for replays
    // parsed too recently to have been archived yet.
    'archive_bucket' => env('DEEP_ANALYTICS_ARCHIVE_BUCKET', 'heroesprofile-replay-archive'),
    'replay_bucket' => env('DEEP_ANALYTICS_REPLAY_BUCKET', 'heroesprofile-replay-storage'),

    // The parser's DeepExtract.Version. Games loaded at an older version are reprocessed on
    // the next request, and cached results are keyed on it.
    'extract_v' => 3,

    // Only games from the start of season 34 on are archived or analysed.
    'cutover' => '2026-10-02 16:00:01',

    // Blizz IDs allowed to use it until access is opened up, comma-separated.
    'allowlist' => array_values(array_filter(array_map('intval', explode(',', (string) env('DEEP_ANALYTICS_ALLOWLIST', '67280'))))),

    // How long one account's request blocks another, in seconds.
    'request_lock_seconds' => 600,

    // How long the page keeps showing a request as in progress if some games never finish.
    'processing_seconds' => 7200,

    // Where the analytics are read from (filled by the deep analytics loader).
    'bigquery_project' => env('DEEP_ANALYTICS_BIGQUERY_PROJECT', 'heroesprofile-244413'),
    'bigquery_dataset' => env('DEEP_ANALYTICS_BIGQUERY_DATASET', 'deep_analytics'),
    'bigquery_location' => 'us-east1',

    // Baselines use replays with replayID % 1000 below this: 50 is the 5% nightly sample.
    // Keep it at or below the sampler's sample_rate * 10 so every baseline game is sampled.
    'baseline_threshold' => env('DEEP_ANALYTICS_BASELINE_THRESHOLD', 50),
];
