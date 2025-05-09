<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\CheckSessionCookie::class,
        ]);
        
        // You can add other middleware registrations here
        // $middleware->alias([...]);
        // $middleware->group([...]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();