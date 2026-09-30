<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiToken
{
    /**
     * Authenticate an API request from an "Authorization: Bearer <token>" header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->bearerToken();

        if (! $header) {
            return response()->json(['message' => 'Unauthenticated. Missing bearer token.'], 401);
        }

        $token = ApiToken::where('token', hash('sha256', $header))->first();

        if (! $token) {
            return response()->json(['message' => 'Invalid or revoked token.'], 401);
        }

        Auth::setUser($token->user);

        $token->update(['last_used_at' => now()]);

        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}