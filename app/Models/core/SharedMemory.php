<?php

namespace App\Models\Core;

use Illuminate\Support\Facades\Cache;

class SharedMemory 
{
    /**
     * Store a value in the cache
     * 
     * @param string $key
     * @param mixed $value
     * @param int $minutes Optional TTL in minutes
     * @return bool
     */
    public function set($key, $value, $minutes = null) 
    {
        if (!is_string($key)) {
            return false;
        }

        if ($minutes) {
            return Cache::put($key, $value, $minutes * 60);
        }
        
        return Cache::put($key, $value);
    }

    /**
     * Retrieve a value from the cache
     * 
     * @param string $key
     * @return mixed
     */
    public function get($key) 
    {
        if (!is_string($key)) {
            return false;
        }

        return Cache::get($key);
    }

    /**
     * Remove a value from the cache
     * 
     * @param string $key
     * @return bool
     */
    public function delete($key) 
    {
        if (!is_string($key)) {
            return false;
        }

        return Cache::forget($key);
    }
}