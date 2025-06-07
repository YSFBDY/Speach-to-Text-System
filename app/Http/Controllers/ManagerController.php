<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Transcription;
use App\Models\Translation;
use App\Models\Screen;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ManagerController extends Controller
{
    public function descriptiverepost(Request $request)
    {
        $range = $request->input('range', 30);
        $plan = $request->input('plan', 'free');

        $allowedRanges = [7, 15, 30];
        $range = in_array($range, $allowedRanges) ? $range : 30;

        $startDate = Carbon::now()->subDays($range)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $usersQuery = User::where('role', 'user')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($plan === 'free') {
        $subscriptionsQuery = Subscription::whereRaw('0=1'); 

        } elseif (is_numeric($plan)) {
            if (!Plan::where('plan_id', $plan)->exists()) {
                return response()->json(['error' => 'Invalid plan'], 400);
            }

            $subscriptionsQuery = Subscription::whereBetween('start_date', [$startDate, $endDate])
                ->where('subscription_status', 'active')
                ->where('plan_id', $plan);
        } else {

            $subscriptionsQuery = Subscription::whereBetween('start_date', [$startDate, $endDate])
                ->where('subscription_status', 'active');
        }

        $totalUsers = $usersQuery->count();
        $totalSubscribedUsers = $subscriptionsQuery->distinct('user_id')->count('user_id');

        // Get all plans (including 'Free')
        $allPlans = Plan::all(['plan_id', 'plan_name'])->toArray();
        $allPlans[] = ['plan_id' => 'free', 'plan_name' => 'Free'];

        // Determine which plans to use based on $plan filter
        if ($plan === null || $plan === 'all') {
            $plans = $allPlans;
        } elseif ($plan === 'free') {
            $plans = [ ['plan_id' => 'free', 'plan_name' => 'Free'] ];
        } elseif (is_numeric($plan)) {
            // Check if the plan exists
            if (!Plan::where('plan_id', $plan)->exists()) {
                return response()->json(['error' => 'Invalid plan'], 400);
            }
            $planObj = collect($allPlans)->firstWhere('plan_id', $plan);
            if (!$planObj) {
                return response()->json(['error' => 'Invalid plan'], 400);
            }
            $plans = [ $planObj ];
        } else {
            $plans = $allPlans;
        }

        // Prepare daily stats
        $dailyStats = [];
        for ($i = 0; $i < $range; $i++) {
            $day = Carbon::now()->subDays($range - $i - 1)->startOfDay();
            $nextDay = $day->copy()->endOfDay();

            // All users created on this day (with role = 'user')
            $usersCreated = User::where('role', 'user')
                ->whereBetween('created_at', [$day, $nextDay])
                ->pluck('user_id')
                ->toArray();
            $totalUsersCount = count($usersCreated);

            foreach ($plans as $planItem) {
                if ($planItem['plan_id'] === 'free') {
                   
                    $subscribedUsersCount = 0;
                } else {
                    // All users who subscribed to this plan on this day (regardless of when created)
                    $subscribedUsersCount = Subscription::whereBetween('start_date', [$day, $nextDay])
                        ->where('subscription_status', 'active')
                        ->where('plan_id', $planItem['plan_id'])
                        ->distinct('user_id')
                        ->count('user_id');
                }

                $dailyStats[] = [
                    'date' => $day->format('Y-m-d'),
                    'total_users' => $totalUsersCount,
                    'subscribed_users' => $subscribedUsersCount,
                    'subscription_plan' => $planItem['plan_name'],
                ];
            }
        }

        return response()->json([
            'total_users' => $totalUsers,
            'total_subscribed_users' => $totalSubscribedUsers,
            'daily' => $dailyStats,
        ]);
    }







    public function explantoryreport(Request $request) {

        $range = $request->input('range', 30);
        $plan = $request->input('plan', 'free');

        $allowedRanges = [7, 15, 30];
        $range = in_array($range, $allowedRanges) ? $range : 30;

        $startDate = Carbon::now()->subDays($range)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $totalUsers = User::where('role', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

         $AvgEng = 0.0;
        if ($plan === 'free') {
            $usersIds = User::where('role', 'user')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNotIn('user_id', Subscription::pluck('user_id')
                )->pluck('user_id');
                
            $totalUsers = count($usersIds);

            if ($totalUsers > 0) {
                $TranscriptionRemaining = User::whereIn('user_id', $usersIds)
                    ->sum('trial_number_transcription');

                $TranslationRemaining = User::whereIn('user_id', $usersIds)
                    ->sum('trial_number_translation');

                $totalTranscriptionUsed = (10 * $totalUsers) - $TranscriptionRemaining;
                $totalTranslationUsed = (10 * $totalUsers) - $TranslationRemaining;

                $AvgEng = floatval(($totalTranscriptionUsed + $totalTranslationUsed) / 2);

            }else {
                $AvgEng = 0.0;
            }

            
        
        } elseif (is_numeric($plan)) {
            Log::info("Plan is numeric: $plan");
            if (!Plan::where('plan_id', $plan)->exists()) {
                return response()->json(['error' => 'Invalid plan'], 400);
            }
            $totalTranscriptionplan = Plan::where('plan_id', $plan)->value('plan_transcription_limit');
            $totalTranslationplan = Plan::where('plan_id', $plan)->value('plan_translation_limit');

            $usersIds = User::where('role', 'user')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('user_id', Subscription::where('plan_id', $plan)
                    ->pluck('user_id')
                )
                ->pluck('user_id');
                
            $totalUsers = count($usersIds);

            if ($totalUsers > 0) {
                $TranscriptionRemaining = Subscription::whereIn('user_id', $usersIds)
                    ->sum('remain_transcription_limit');

                $TranslationRemaining = Subscription::whereIn('user_id', $usersIds)
                    ->sum('remain_translation_limit');

                $totalTranscriptionUsed = ($totalTranscriptionplan * $totalUsers) - $TranscriptionRemaining;
                $totalTranslationUsed = ($totalTranslationplan * $totalUsers) - $TranslationRemaining;

                $AvgEng = floatval(($totalTranscriptionUsed + $totalTranslationUsed) / 2);

            }else {
                $AvgEng = 0.0;
            }
            

        } 



        return response()->json([
            'total_users' => $totalUsers,
            'AvgEngagement' => $AvgEng,
        ]);

        
    }




}
