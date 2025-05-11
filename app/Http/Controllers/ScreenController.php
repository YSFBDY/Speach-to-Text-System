<?php

namespace App\Http\Controllers;

use App\Models\Screens;
use App\Models\User;

use Illuminate\Http\Request;
use App\Http\Requests\Screen\CreateScreenRequest;
use App\Models\Transcription;
use App\Models\Translation;
use App\Traits\TimeAgo;
class ScreenController extends Controller
{
    use TimeAgo;
    public function createscreen(CreateScreenRequest $request) {
        
        if (! User::where('user_id', $request->user_id)->first() || !$request->user_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $screen = Screens::create([
            'user_id' => $request->user_id,
            'screen_name' => $request->screen_name
        ]);

        return response()->json([
            'screen_id' => $screen->screen_id
        ],201);

    }

    public function leavefeedback(Request $request) {

        $screen = Screens::where('screen_id', $request->screen_id)->first();

        if (!$screen || !$request->screen_id) {
            return response()->json([
                'message' => 'Screen not found'
            ],404);
        }
        if ($request->feedback <1 || $request->feedback >5) {
            return response()->json([
                'message' => 'Feedback must be between 1 and 5'
            ],400);
        }
        $screen->feedback = $request->feedback;
        $screen->save();

        return response()->json([
            'message' => 'Feedback submitted successfully'
        ],201);
    }





    public function showscreens($user_id) {

        if (! User::where('user_id', $user_id)->first() || !$user_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $allscreens = Screens::where('user_id', $user_id)
        ->orderBy('created_at', 'desc')
        ->get();

        if (!$allscreens) {
            return response()->json(status: 204);
        }

        $screens = [];
    
        foreach ($allscreens as $screen) {
            $screens[] = [
                "screen_id" => $screen->screen_id,
                "screen_name" => $screen->screen_name,
                "time_ago" => $this->getTimeAgo($screen->created_at),
            ];
        }
    
        return response()->json($screens, 200);
    }





    public function showscreen($screen_id)
    {

        $screen = Screens::where('screen_id', $screen_id)
            ->select('screen_id', 'screen_name', 'feedback')
            ->first();
    
        if (!$screen || !$screen_id) {
            return response()->json([
                'message' => 'Screen not found'
            ],404);
        }
    

            $transcriptions = Transcription::where('screen_id', $screen_id)->get();
    

        $translations = Translation::whereIn('transcription_id', $transcriptions->pluck('transcription_id'))->get();
    

        $formattedScreen = $transcriptions->map(function ($transcription) use ($translations) {

            $transcriptionTranslations = $translations->where('transcription_id', $transcription->transcription_id);
    
            $formattedTranslations = $transcriptionTranslations->map(function ($translation) {
                return [
                    "translation_id" => $translation->translation_id ?? null,
                    "target_language" => $translation->target_language ?? null,
                    "translated_text" => $translation->translated_text ?? null,
                ];
            })->values(); 
    
            return [
                "transcription_id" => $transcription->transcription_id,
                "audio_path" => $transcription->audio_path,
                "text_content" => $transcription->text_content,
                "time_ago" => $this->getTimeAgo($transcription->created_at),
                "translations" => $formattedTranslations // This will now be an array of translations
            ];
        });
    
        return response()->json([
            "screen_id" => $screen->screen_id,
            "screen_name" => $screen->screen_name,
            "feedback" => $screen->feedback,
            "transcriptions" => $formattedScreen
        ], 200);
    }      



    public function deletescreen($screen_id) {   

        $screen = Screens::where('screen_id', $screen_id)->first();

        if (!$screen) {
            return response()->json([
                'message' => 'Screen not found'
            ],404);
        }

        $screen->delete();

        return response()->json([
            'message' => 'Screen deleted successfully'
        ],200);


    }





}
