<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // Add new disks for user files
        'user_files' => [
            'driver' => 'local',
            'root' => storage_path('app/user_files'),
            'url' => env('APP_URL') . '/storage/user_files',
            'visibility' => 'private',
        ],
        'user_templates' => [
            'driver' => 'local',
            'root' => storage_path('app/user_templates'),
            'url' => env('APP_URL') . '/storage/user_templates',
            'visibility' => 'private',
        ],
        'user_id_copies' => [
            'driver' => 'local',
            'root' => storage_path('app/user_id_copies'),
            'url' => env('APP_URL') . '/storage/user_id_copies',
            'visibility' => 'private',
        ],
        'user_birth_certificates' => [
            'driver' => 'local',
            'root' => storage_path('app/user_birth_certificates'),
            'url' => env('APP_URL') . '/storage/user_birth_certificates',
            'visibility' => 'private',
        ],
        'user_gs_letters' => [
            'driver' => 'local',
            'root' => storage_path('app/user_gs_letters'),
            'url' => env('APP_URL') . '/storage/user_gs_letters',
            'visibility' => 'private',
        ],
        'user_police_reports' => [
            'driver' => 'local',
            'root' => storage_path('app/user_police_reports'),
            'url' => env('APP_URL') . '/storage/user_police_reports',
            'visibility' => 'private',
        ],
        'user_ndas' => [
            'driver' => 'local',
            'root' => storage_path('app/user_ndas'),
            'url' => env('APP_URL') . '/storage/user_ndas',
            'visibility' => 'private',
        ],
        'bonds' => [
            'driver' => 'local',
            'root' => storage_path('app/bonds'),
            'url' => env('APP_URL') . '/storage/bonds',
            'visibility' => 'private',
        ],
        'user_appointment_letters' => [
            'driver' => 'local',
            'root' => storage_path('app/user_appointment_letters'),
            'url' => env('APP_URL') . '/storage/user_appointment_letters',
            'visibility' => 'private',
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
