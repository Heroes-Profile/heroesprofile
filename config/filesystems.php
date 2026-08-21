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
        'gcs' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
            'visibility' => 'private',
        ],

        /* NGS replays live in their own bucket, separate from uploader replays. */
        'gcs-ngs' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'bucket' => env('GOOGLE_CLOUD_NGS_BUCKET', 'heroesprofile-ngs'),
            'visibility' => 'private',
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
