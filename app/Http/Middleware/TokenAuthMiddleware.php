<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class TokenAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken(); // Get token from Authorization header

        if (!$token) {
            Log::error('Token not found');
            abort(401);
        }

        // Validate the token
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            Log::error('Invalid token');
            abort(401);
        }

        // Authenticate user
        auth()->guard('sanctum')->setUser($accessToken->tokenable);
        $request->setUserResolver(fn () => $accessToken->tokenable);

        return $next($request);
    }
}
