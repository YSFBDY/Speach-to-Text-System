<?php

namespace App\Http\Controllers;

use App\Models\Transcription;
use App\Models\Subscription;
use App\Models\Screens;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Requests\Transcirption\TranscriptionRequest;
use App\Models\User;
use App\Traits\HandleFiles;
class TranscriptionController extends Controller
{
    use HandleFiles;

    public function transcribe(TranscriptionRequest $request)
    {
        if(! User::where('user_id', $request->user_id)->exists() || !$request->user_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if(! Screens::where('screen_id', $request->screen_id)->exists() || !$request->screen_id) {
            return response()->json(['message' => 'Screen not found'], 404);
        }

        $audio = $request->file('audio');

        if (!$audio) {
            return response()->json(['error' => 'No audio file uploaded'], 400);
        }

        $response = Http::withoutVerifying()
        ->attach(
            'file', file_get_contents($audio), $audio->getClientOriginalName()
        )->post(env('FASTAPI_URL'));

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to transcribe audio'], 500);
        }

        $responseData = $response->json();
        $textContent = $responseData['transcription'] ?? null;

        $audioname = $this->handleFileUpload($request, 'audio', 'audio');


        if ($textContent) {
            $transcription = Transcription::create([
                'audio_path' => $audioname,
                'text_content' => $textContent,
                'user_id' => $request->user_id,
                'screen_id' => $request->screen_id
            ]);
        }



        $subscription = Subscription::where('user_id', $request->user_id)
            ->first();
        $activesubscription = Subscription::where('user_id', $request->user_id) ->where('subscription_status', 'active')->first();

        if (!$subscription) {   
            User::where('user_id', $request->user_id)->decrement('trial_number_transcription');

        }elseif($activesubscription)
        {
            $activesubscription->decrement('remain_transcription_limit');
        }
    
        return response()->json([
            "transcription_id" => $transcription->transcription_id ?? null,
            "text_content" => $textContent, 
            ], 200);
          
         
    }

}
