<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ClearViewCache; // Add this import

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\CheckSessionCookie::class,
            \App\Http\Middleware\ClearViewCache::class, // Add your middleware here
        ]);
        
        // You can add other middleware registrations here
        // $middleware->alias([...]);
        // $middleware->group([...]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();