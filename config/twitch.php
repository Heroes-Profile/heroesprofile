<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Twitch Extension
    |--------------------------------------------------------------------------
    |
    | The Heroes Profile Twitch extension shows a streamer's live lobby — players,
    | heroes, talent picks and lobby stats — to their viewers.
    |
    | Viewers never call this site. The uploader posts a snapshot here, and one
    | scheduled push hands it to Twitch, which fans it out to every viewer through
    | Extension PubSub and serves it to late joiners from the developer
    | configuration segment. Audience size costs us nothing; the first version
    | queried the database per viewer and a 10k-viewer channel locked it.
    |
    */

    // The extension's own client id and OAuth secret, from the Twitch developer
    // console. The same app is used for the portal's "Connect Twitch" sign-in.
    'client_id' => env('TWITCH_EXTENSION_CLIENT_ID'),
    'client_secret' => env('TWITCH_EXTENSION_CLIENT_SECRET'),

    // Twitch user id of the extension owner. Sent as `user_id` in the external JWT
    // signed for Helix extension endpoints.
    'owner_user_id' => env('TWITCH_EXTENSION_OWNER_ID'),

    // Base64 extension secrets from the console, current first. Comma separated so
    // a rotation can accept the old one until Twitch retires it. Signing always
    // uses the first.
    'extension_secrets' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TWITCH_EXTENSION_SECRETS', ''))
    ))),

    // Version of the extension released on Twitch, for Set Required Configuration.
    'extension_version' => env('TWITCH_EXTENSION_VERSION', '2.0.0'),

    // Until Twitch has approved the extension nobody else can add it to a channel,
    // so the public page and the portal's setup section are admins only. Endpoints
    // stay open, so a channel already set up keeps working.
    'public_enabled' => env('TWITCH_PUBLIC_ENABLED', false),

    // Free trial, once per Twitch channel, starting at the first accepted snapshot.
    'trial_days' => 30,

    // Upper bound on the anti-stream-snipe delay a streamer may set, in seconds.
    'max_delay' => 900,

    // Twitch caps a PubSub message and a configuration segment at 5KB each.
    'max_payload_bytes' => 5120,

    // Snapshot ingest, per uploader key.
    'snapshots_per_minute' => 20,

    // Bumped whenever the streamer code of conduct changes. Listed streamers must
    // accept the current version or they drop off the public page.
    'listing_terms_version' => 1,

    // Static hero/talent data the extension resolves ids from, written by
    // `php artisan twitch:game-data` after each patch. The extension's build copies
    // it into the bundle; viewers never fetch it from here.
    'game_data_path' => 'static/twitch/game-data.json',

    // What the last extension release bundled, written by its `npm run release`.
    // `twitch:check-extension` and the snapshot ingest compare the site against it.
    'extension_manifest_path' => 'resources/twitch/extension-manifest.json',

    // Cloud Tasks queue for the delayed viewer push. Shares the project, service
    // account and audience with the global query queue in `global.cloud_tasks`.
    'push_queue' => env('TWITCH_PUSH_QUEUE', 'twitch-push'),

    // Where those tasks are sent. Defaults to this app's own internal push route.
    'push_handler_url' => env('TWITCH_PUSH_HANDLER_URL'),

    // How long the public streamer directory is cached, in seconds.
    'directory_cache_seconds' => 180,

];
