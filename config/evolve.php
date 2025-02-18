<?php

return [

    'paths' => [
        'base_url' => env('EVOLVE_BASE_URL', '/payroll_v2/interface'),
        'log' => env('EVOLVE_LOG_PATH', 'C:/xampp_7.4.23/htdocs/payroll_v2/log'),
        'storage' => env('EVOLVE_STORAGE_PATH', 'C:/xampp_7.4.23/htdocs/payroll_v2/storage'),
        'user_file_path' => env('EVOLVE_USER_FILE_PATH', 'C:/xampp_7.4.23/htdocs/payroll_v2/storage'),
        'php_cli' => env('EVOLVE_PHP_CLI', 'C:/xampp_7.4.23/php/php.exe'),
    ],

    'database' => [
        'type' => env('EVOLVE_DB_TYPE', 'mysqli'),
        'host' => env('EVOLVE_DB_HOST', 'localhost'),
        'database_name' => env('EVOLVE_DB_NAME', 'payroll_kdu'),
        'user' => env('EVOLVE_DB_USER', 'root'),
        'password' => env('EVOLVE_DB_PASSWORD', ''),
    ],

    'mail' => [
        'delivery_method' => env('EVOLVE_MAIL_METHOD', 'smtp'),
        'smtp_host' => env('EVOLVE_SMTP_HOST', 'smtp.gmail.com'),
        'smtp_port' => env('EVOLVE_SMTP_PORT', 465),
        'smtp_username' => env('EVOLVE_SMTP_USERNAME', 'careers@aquafresh.lk'),
        'smtp_password' => env('EVOLVE_SMTP_PASSWORD', '123456789'),
    ],

    'cache' => [
        'enable' => env('EVOLVE_CACHE_ENABLE', true),
        'dir' => env('EVOLVE_CACHE_DIR', 'C:/xampp_7.4.23/htdocs/payroll_v2/temp'),
    ],

    'debug' => [
        'production' => env('EVOLVE_DEBUG_PRODUCTION', false),
        'enable' => env('EVOLVE_DEBUG_ENABLE', true),
        'enable_display' => env('EVOLVE_DEBUG_DISPLAY', true),
        'buffer_output' => env('EVOLVE_DEBUG_BUFFER', true),
        'enable_log' => env('EVOLVE_DEBUG_LOG', false),
        'verbosity' => env('EVOLVE_DEBUG_VERBOSITY', 10),
    ],

    'other' => [
        'force_ssl' => env('EVOLVE_FORCE_SSL', false),
        'installer_enabled' => env('EVOLVE_INSTALLER_ENABLED', false),
        'primary_company_id' => env('EVOLVE_PRIMARY_COMPANY_ID', 1),
        'hostname' => env('EVOLVE_HOSTNAME', 'localhost'),
        'salt' => env('EVOLVE_SALT', '6aee37c77b7d8d22923624961c4f6cba'),
    ],
];
