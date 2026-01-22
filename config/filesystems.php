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

    // Add custom disk for journal files
    'journals' => [
        'driver' => 'local',
        'root' => storage_path('app/journals'),
        'url' => env('APP_URL').'/storage/journals',
        'visibility' => 'private',
        'throw' => false,
    ],

    'annexes' => [
        'driver' => 'local',
        'root' => storage_path('app/annexes'),
        'url' => env('APP_URL').'/storage/annexes',
        'visibility' => 'private',
        'throw' => false,
    ],

    'figures' => [
        'driver' => 'local',
        'root' => storage_path('app/figures'),
        'url' => env('APP_URL').'/storage/figures',
        'visibility' => 'private',
        'throw' => false,
    ],

    'profile' => [
        'driver' => 'local',
        'root' => storage_path('app/profile'),
        'url' => env('APP_URL').'/storage/profile',
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
