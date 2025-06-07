<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Transcription;
use App\Models\Translation;
use App\Models\Payment;

use App\Http\Requests\Manager\descriptiverepostRequest;
use App\Http\Requests\Manager\explantoryreportRequest;
use App\Http\Requests\Manager\financialreportRequest;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ManagerController extends Controller
{
    public function descriptiverepost(descriptiverepostRequest $request)
    {
        $range = $request->input('range', 90);
        $plan = $request->input('plan', 'free');

        $allowedRanges = [7, 15, 30, 60, 90];
        $range = in_array($range, $allowedRanges) ? $range : 90;

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







    public function explantoryreport(explantoryreportRequest $request) {

        $range = $request->input('range', 90);

        $allowedRanges = [7, 15, 30 , 60, 90];
        $range = in_array($range, $allowedRanges) ? $range : 90;

        $startDate = Carbon::now()->subDays($range)->startOfDay();
        $endDate = Carbon::now()->endOfDay();


        $allPlans = Plan::all(['plan_id', 'plan_name'])->toArray();
        $allPlans[] = ['plan_id' => 'free', 'plan_name' => 'Free'];

        $stats = [];
        foreach ($allPlans as $plan) {
            if ($plan['plan_id'] === 'free') {
                $usersIds = User::where('role', 'user')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotIn('user_id', Subscription::pluck('user_id'))
                    ->pluck('user_id');
            } else {
                $usersIds = User::where('role', 'user')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('user_id', Subscription::where('plan_id', $plan['plan_id'])->pluck('user_id'))
                    ->pluck('user_id');
            }

            $planTotalUsers = count($usersIds);

            $transcriptionCount = Transcription::whereIn('user_id', $usersIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $transcriptionIds = Transcription::whereIn('user_id', $usersIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->pluck('transcription_id');

            $translationCount = Translation::whereIn('transcription_id', $transcriptionIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $stats[] = [
                'plan' => $plan['plan_name'],
                'total_users' => $planTotalUsers,
                'transcription_count' => $transcriptionCount,
                'translation_count' => $translationCount,
            ];
        }

        // Calculate overall total users for the period (not per plan)
        $overallTotalUsers = User::where('role', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return response()->json([
            'total_users' => $overallTotalUsers,
            'stats' => $stats,
        ]);

        
    }



    public function financialreport(financialreportRequest $request) {

        $range = $request->input('range', 90);
        $paymentStatus = $request->input('payment_status', 'all');

        $allowedPaymentStatuses = ['all', 'pending', 'approved', 'declined'];
        $paymentStatus = in_array($paymentStatus, $allowedPaymentStatuses) ? $paymentStatus : 'all';

        $allowedRanges = [7, 15, 30, 60, 90];
        $range = in_array($range, $allowedRanges) ? $range : 90;

        $startDate = Carbon::now()->subDays($range)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $users = User::where('role', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->pluck('user_id');

        $totalUsers = $users->count();

        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])->get();

        // Filter payments by status if needed
        if ($paymentStatus !== 'all') {
            $payments = $payments->where('payment_status', $paymentStatus);
        }

        $totalPayments = $payments->count();
        $totalAmount = $payments->where('payment_status', 'approved')->sum('payment_amount');

        $paymentsData = [];
        foreach ($payments as $payment) {
            $subscription = Subscription::where('user_id', $payment->user_id)
                ->where('plan_id', $payment->plan_id)
                ->orderByDesc('start_date')
                ->first();

            if ($subscription) {
                $startDateVal = $subscription->start_date;
                $endDateVal = $subscription->end_date;
                $planName = Plan::where('plan_id', $subscription->plan_id)->value('plan_name') ?? 'N/A';
            } else {
                $startDateVal = 'N/A';
                $endDateVal = 'N/A';
                $planName = 'N/A';
            }

            $paymentsData[] = [
                'user_id' => $payment->user_id,
                'user_name' => $payment->first_name . ' ' . $payment->last_name,
                'payment_id' => $payment->payment_id,
                'payment_amount' => $payment->payment_amount . ' EGP',
                'payment_date' => $payment->created_at->format('Y-m-d'),
                'payment_status' => $payment->payment_status,
                'plan_name' => $planName,
                'start_date' => $startDateVal,
                'end_date' => $endDateVal,
            ];
        }

        return response()->json([
            'total_users' => $totalUsers,
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount . 'EGP',
            'payments_data' => $paymentsData
        ]);
    }





}
