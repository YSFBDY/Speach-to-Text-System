<?php

namespace App\Http\Controllers;

use App\Models\Plan;

use Illuminate\Http\Request;
use App\Http\Requests\Plan\CreatePlanRequest;

class PlanController extends Controller
{
    public function createplan(CreatePlanRequest $request) {

        $plan = Plan::create([
            'plan_name' => $request->plan_name,
            'plan_price' => $request->plan_price,
            'plan_period' => $request->plan_period,
            'plan_description' => $request->plan_description,
            'plan_transcription_limit' => $request->plan_transcription_limit,
            'plan_translation_limit' => $request->plan_translation_limit,
            'plan_price_cents' => $request->plan_price_cents
        ]);

        return response()->json(["message" => "Plan created successfully"],201);

    }




    public function showplans() {

        $plans = Plan::select('plan_id','plan_name', 'plan_price', 'plan_period', 'plan_description', 'plan_transcription_limit', 'plan_translation_limit')->get();

        return response()->json(["plans"  => $plans]);
        
    }



}
