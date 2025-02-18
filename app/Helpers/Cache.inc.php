<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class CustomCacheService
{
    public function initializeCache()
    {
        // If caching is disabled, still do memory caching, otherwise permission checks cause the page to take 2+ seconds to load
        if (config('cache.enable') === false) {
            Config::set('cache.only_memory_cache_enable', true);
        } else {
            Config::set('cache.only_memory_cache_enable', false);
        }

        // Set custom cache options
        $cacheOptions = [
            'lifetime' => 86400, // 1 day in seconds
            'fileLocking' => true,
            'writeControl' => true,
            'readControl' => true,
            'memoryCaching' => true,
            'onlyMemoryCaching' => config('cache.only_memory_cache_enable'),
            'automaticSerialization' => true,
            'hashedDirectoryLevel' => 1,
            'fileNameProtection' => false,
        ];

        // You can now modify the configuration settings directly or apply cache options
        // directly through Cache facade if needed.

        // Cache using the file store
        $cache = Cache::store('file');

        // Now, just use it directly without needing setOptions
        // Example of caching data with the custom options
        Cache::put('your-cache-key', 'your-data', now()->addSeconds($cacheOptions['lifetime']));

        return $cache;
    }
}
