<?php

// app/Http/Middleware/ClearViewCache.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Artisan;

class ClearViewCache
{
    public function handle($request, Closure $next)
    {
        if (app()->environment('local')) {
            Artisan::call('view:clear');
        }
        return $next($request);
    }
}

?>