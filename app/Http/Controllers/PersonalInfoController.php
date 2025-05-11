<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Subscription;

class PersonalInfoController extends Controller
{
    public function showlimits($user_id)
    {
        if (!User::where("user_id", $user_id)->exists() || !$user_id) {
            return response()->json(["message" => "User not found"], 404);
        }

        $sub_limit=Subscription::where("user_id", $user_id)->where("subscription_status", "active")->first();

        if(!$sub_limit){
        $user = User::select("trial_number_transcription", "trial_number_translation")->where("user_id", $user_id)->first();
        return response()->json([
            "remain_transcription_limit" => $user->trial_number_transcription,
            "remain_translation_limit" => $user->trial_number_translation
        ]);
        }else{
            return response()->json([
                "remain_transcription_limit" => $sub_limit->remain_transcription_limit,
                "remain_translation_limit" => $sub_limit->remain_translation_limit3
            ]);
        }
    }


}
