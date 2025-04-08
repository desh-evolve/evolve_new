<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionCookie
{
    // Routes that should be excluded from session checking
    protected $except = [
        'login',
        'logout',
        'authenticate',
        'password.request', // Forgot password
        'password.reset',  // Password reset
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for excluded routes
        if (in_array($request->route()->getName(), $this->except)) {
            return $next($request);
        }

        if (!$request->cookies->has('SessionID')) {
            return redirect()->route('login', ['session_expired' => true]);
        }

        return $next($request);
    }
}