<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ManagerAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user= User::find($request->user_id);
        
        if (!$user || $user->role !== 'manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return $next($request);
    }
}
