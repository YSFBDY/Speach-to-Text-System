<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


use App\Models\Subscription;
use App\Models\User;

class AccessToTranslationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::where('user_id', $request->user_id)->first();

        if (!$user || !$request->user_id) {
            return response()->json(['error' => 'User not found'], 404);
        }


        $subscription = Subscription::where('user_id', $user->user_id)
            ->first();
    
        if (!$subscription) {
            // No subscription, check trial limit
            if ($user->trial_number_translation > 0) {
                return $next($request);
            } else {
                return response()->json(['error' => 'Translation Trial limit exceeded. Please subscribe.'], 403);
            }
        }
    

        $activeSubscription = Subscription::where('user_id', $user->user_id)
            ->where('subscription_status', 'active')
            ->first();
        if ($activeSubscription) {
            // Subscription active, check transcription limit
            if ($activeSubscription->remain_translation_limit > 0) {
                return $next($request);
            } else {
                return response()->json(['error' => 'Translation limit exceeded. Please upgrade.'], 406);
            }
        } else {
            // Subscription expired
            return response()->json(['error' => 'Subscription expired. Please renew.'], 409);
        }
        
    }
    
}
