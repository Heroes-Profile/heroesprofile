<?php

use Illuminate\Support\Str;

// With a CA set, verify the server's certificate and name. HEROESPROFILE_DB_HOST must be
// the instance's .sql.goog name from its certificate, not the IP. TLS is also what lets
// caching_sha2_password log in on a cold auth cache.
$sslVerify = env('MYSQL_ATTR_SSL_CA') && extension_loaded('pdo_mysql')
    ? [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true]
    : [];

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('HEROESPROFILE_DB_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'heroesprofile' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],
        /*
         * The API site's own schema: accounts, API keys, subscriptions, and the
         * endpoint registry that drives per-plan quotas.
         *
         * Note the collation differs from the connections above. This schema is
         * utf8mb4_0900_ai_ci, not utf8mb4_unicode_ci — mismatching it produces
         * "Illegal mix of collations" on any join against these tables.
         */
        'heroesprofile_api' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_API'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_0900_ai_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        /*
         * Backend job settings (processing_check: pause switches, the analytics
         * sample rate). The site only reads it.
         */
        'heroesprofile_processing' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_PROCESSING', 'heroesprofile_processing'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        /*
         * Community polls and their votes.
         */
        'heroesprofile_polls' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_POLLS', 'heroesprofile_polls'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        'heroesprofile_globals' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_GLOBALS'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        'heroesprofile_ngs' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_NGS'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        /*
        | NGS uploads carry `mode=prod|dev`, which the old API used to pick between
        | the live NGS schema and a scratch copy. Same switch, same two schemas.
        */
        'heroesprofile_ngs_dev' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_NGS_DEV'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        'heroesprofile_ccl' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_CCL'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        'heroesprofile_mcl' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_MCL'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        'heroesprofile_hi' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_HI'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        'heroesprofile_hi_nc' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_HI_NC'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        'heroesprofile_esports_other' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_DATABASE_HGC_MORE'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],

        'heroesprofile_cache' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_CACHE_DATABASE'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],
        'heroesprofile_logs' => [
            'driver' => 'mysql',
            'host' => env('HEROESPROFILE_DB_HOST'),
            'port' => env('HEROESPROFILE_DB_PORT', '3306'),
            'database' => env('HEROESPROFILE_DB_LOGGING_DATABASE'),
            'username' => env('HEROESPROFILE_DB_USERNAME'),
            'password' => env('HEROESPROFILE_DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB ROW_FORMAT=DYNAMIC',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) + $sslVerify : [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
