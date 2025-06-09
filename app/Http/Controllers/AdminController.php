<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;


class AdminController extends Controller
{
    
    public function UserManagement()
    {
        $users = User::where('role', 'user')->get();

        $usersData = $users->map(function ($user) {
        $transcriptions=0;
        $traslations=0;
        $staus=null;
            if(Subscription::where('user_id', $user->user_id)->doesntExist()){
                $transcriptions = 10 - $user->trial_number_transcription;
                $traslations = 10 - $user->trial_number_translation;
            }else{
                $subscription = Subscription::where('user_id', $user->user_id)
                ->where('subscription_status', 'active')->first();
                $plan = Plan::where('plan_id', $subscription->plan_id)->first();
                $transcriptions = $plan->plan_transcription_limit - $subscription->remain_transcription_limit;
                $traslations =  $plan->plan_translation_limit - $subscription->remain_translation_limit;
            }

            $lastToken = PersonalAccessToken::where('tokenable_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->first();
            if (!$lastToken) {
                return [
                    'user_id' => $user->user_id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'transcriptions' => $transcriptions,
                    'traslations' => $traslations,
                    'created_at' => Carbon::parse($user->created_at)->format('d/m/Y'),
                    'last_active' => 'N/A',
                    'status' => 'inactive'
                ];
            }
            if ($lastToken->created_at->lt(now()->subDays(30))) {
                $staus = 'inactive';
            }else{
                $staus = 'active';
            }

                return [
                'user_id' => $user->user_id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'transcriptions' => $transcriptions,
                'traslations' => $traslations,
                'created_at' => Carbon::parse($user->created_at)->format('d/m/Y'),
                'last_active' => Carbon::parse($lastToken->created_at)->format('d/m/Y'),
                'status' => $staus

            ];


        });


        return response()->json($usersData, 200);
    }


}
