<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://www.heroesprofile.com',
        'https://heroesprofile.com',
        'https://develop.heroesprofile.com',
    ],

    // The Twitch extension is served from <client id>.ext-twitch.tv. Only its
    // broadcaster config view calls us; viewers never do.
    'allowed_origins_patterns' => array_filter([
        env('TWITCH_EXTENSION_CLIENT_ID') ? '#^https://'.preg_quote(env('TWITCH_EXTENSION_CLIENT_ID'), '#').'\.ext-twitch\.tv$#' : null,
    ]),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
