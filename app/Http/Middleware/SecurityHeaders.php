<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' http://localhost:5173 https://cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline' http://localhost:5173 https://cdn.jsdelivr.net https://fonts.bunny.net; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' https://fonts.bunny.net https://cdn.jsdelivr.net; " .
            "connect-src 'self' http://localhost:5173 ws://localhost:5173; " .
            "frame-ancestors 'self'; " .
            "base-uri 'self'; " .
            "form-action 'self'"
        );

        return $response;
    }
}