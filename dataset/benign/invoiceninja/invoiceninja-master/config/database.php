<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

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

    'default' => env('DB_TYPE', 'mysql'),

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

        // single database setup
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE', 'forge'),
            'username'  => env('DB_USERNAME', 'forge'),
            'password'  => env('DB_PASSWORD', ''),
            'port'      => env('DB_PORT', '3306'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => env('DB_STRICT', false),
            'engine'    => 'InnoDB',
        ],

        // multi-database setup
        'db-ninja-0' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST0', env('DB_HOST', 'localhost')),
            'database'  => env('DB_DATABASE0', env('DB_DATABASE', 'forge')),
            'username'  => env('DB_USERNAME0', env('DB_USERNAME', 'forge')),
            'password'  => env('DB_PASSWORD0', env('DB_PASSWORD', '')),
            'port'      => env('DB_PORT0', env('DB_PORT', '3306')),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => env('DB_STRICT', false),
            'engine'    => 'InnoDB',
        ],

        'db-ninja-1' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST1', env('DB_HOST', 'localhost')),
            'database'  => env('DB_DATABASE1', env('DB_DATABASE', 'forge')),
            'username'  => env('DB_USERNAME1', env('DB_USERNAME', 'forge')),
            'password'  => env('DB_PASSWORD1', env('DB_PASSWORD', '')),
            'port'      => env('DB_PORT1', env('DB_PORT', '3306')),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => env('DB_STRICT', false),
            'engine'    => 'InnoDB',
        ],

        'db-ninja-2' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST2', env('DB_HOST', 'localhost')),
            'database'  => env('DB_DATABASE2', env('DB_DATABASE', 'forge')),
            'username'  => env('DB_USERNAME2', env('DB_USERNAME', 'forge')),
            'password'  => env('DB_PASSWORD2', env('DB_PASSWORD', '')),
            'port'      => env('DB_PORT2', env('DB_PORT', '3306')),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => env('DB_STRICT', false),
            'engine'    => 'InnoDB',
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
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => 6379,
            'database' => 0,
        ],

    ],

];
