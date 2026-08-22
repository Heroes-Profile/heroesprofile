<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        /*
         * Replay storage. Objects are named by fingerprint, not replay id.
         * Requires spatie/laravel-google-cloud-storage.
         *
         * No key file on purpose. Cloud Run exposes its runtime service account
         * through the metadata server, so the client authenticates by Application
         * Default Credentials — nothing to ship, nothing to rotate. Adding a
         * `key_file` here is what makes it look for a JSON file instead.
         *
         * Locally, `gcloud auth application-default login` provides the same.
         */
        /*
         * `throw` is on because the default turns a failed write into a `false`
         * return. An upload that silently does not reach the bucket surfaces
         * later as the parser reporting "No such object", which points at the
         * wrong component entirely.
         *
         * `key_file_path` is a local development escape hatch and nothing else.
         * The variable is not set in Cloud Run, and the adapter only reads the
         * option when it is truthy, so production falls straight through to the
         * runtime service account on the metadata server — no key to ship and
         * none to rotate. Point it at a key file outside this repository.
         */
        'gcs' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
            'key_file_path' => env('GOOGLE_CLOUD_KEY_FILE'),
            'visibility' => 'private',
            'throw' => true,
        ],

        /*
         * Esport replays, each in its own bucket and named by replay id rather
         * than fingerprint. Only the site's own download route reaches these —
         * the public API does not serve esport replays.
         */
        'gcs-ccl' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'bucket' => env('GOOGLE_CLOUD_CCL_BUCKET', 'heroesprofile-ccl'),
            'key_file_path' => env('GOOGLE_CLOUD_KEY_FILE'),
            'visibility' => 'private',
            'throw' => true,
        ],

        'gcs-esport-other' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'bucket' => env('GOOGLE_CLOUD_ESPORT_OTHER_BUCKET', 'heroesprofile-esport-other'),
            'key_file_path' => env('GOOGLE_CLOUD_KEY_FILE'),
            'visibility' => 'private',
            'throw' => true,
        ],

        /* NGS replays live in their own bucket, separate from uploader replays. */
        'gcs-ngs' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'bucket' => env('GOOGLE_CLOUD_NGS_BUCKET', 'heroesprofile-ngs'),
            'key_file_path' => env('GOOGLE_CLOUD_KEY_FILE'),
            'visibility' => 'private',
            'throw' => true,
        ],

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
