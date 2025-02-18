<?php

namespace App\Models\Core;

use Illuminate\Support\Facades\Config;

class Environment
{
    protected static $templateDir = 'templates';
    protected static $templateCompileDir = 'templates_c';

    public static function getBasePath()
    {
        return base_path(); // Uses Laravel’s base_path() helper
    }

    public static function getBaseURL()
    {
        return config('app.url', '/') . '/';
    }

    public static function getAPIBaseURL($api = null)
    {
        $baseUrl = config('app.url');

        if (!$api) {
            $api = match (true) {
                defined('TIMETREX_AMF_API') && TIMETREX_AMF_API => 'amf',
                defined('TIMETREX_SOAP_API') && TIMETREX_SOAP_API => 'soap',
                defined('TIMETREX_JSON_API') && TIMETREX_JSON_API => 'json',
                default => 'rest',
            };
        }

        return rtrim($baseUrl, '/') . '/api/' . $api . '/';
    }

    public static function getAPIURL($api)
    {
        return self::getAPIBaseURL($api) . 'api.php';
    }

    public static function getTemplateDir()
    {
        return self::getBasePath() . DIRECTORY_SEPARATOR . self::$templateDir . DIRECTORY_SEPARATOR;
    }

    public static function getTemplateCompileDir()
    {
        return self::getBasePath() . DIRECTORY_SEPARATOR . self::$templateCompileDir . DIRECTORY_SEPARATOR;
    }

    public static function getImagesPath()
    {
        return public_path('images') . DIRECTORY_SEPARATOR;
    }

    public static function getImagesURL()
    {
        return asset('images') . '/';
    }

    public static function getStorageBasePath()
    {
        return storage_path();
    }

    public static function getUserFileStorageBasePath()
    {
        return storage_path('user_files');
    }

    public static function getLogBasePath()
    {
        return storage_path('logs');
    }
}
