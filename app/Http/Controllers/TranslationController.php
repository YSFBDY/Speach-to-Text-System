<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use App\Models\Transcription;
use App\Models\User;
use App\Models\Subscription;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Requests\Translation\TranslationRequest;

class TranslationController extends Controller
{
    public function translate(TranslationRequest $request)
    {

        if (!$request->user_id || ! User::where('user_id', $request->user_id)->exists()) {
            return response()->json(['message' => 'User Not Found'], 400);
        }

        if (!$request->transcription_id || ! Transcription::where('transcription_id', $request->transcription_id)->exists()) {
            return response()->json(['message' => 'Transcription Not Found'], 400);
        }

        $text = Transcription::where('transcription_id', $request->transcription_id)->value("text_content");
        $response = Http::withoutVerifying()->get('https://translation.googleapis.com/language/translate/v2', [
            'q' => $text,
            'target' => $request->target_language_code,
            'format' => 'text',
            'key' => env('GOOGLE_TRANSLATE_API_KEY'),
        ]);

        $translatedText = $response['data']['translations'][0]['translatedText'] ?? null;

        if ($translatedText) {
            $translation=Translation::create([
                        'transcription_id' => $request->transcription_id,
                        'target_language' => $request->target_language,
                        'target_language_code' => $request->target_language_code,
                        'translated_text' => $translatedText,
                    ]);
        }


        $subscription = Subscription::where('user_id', $request->user_id) ->first();

        $activesubscription = Subscription::where('user_id', $request->user_id) ->where('subscription_status', 'active')->first();

        if (!$subscription) {   
            User::where('user_id', $request->user_id)->decrement('trial_number_translation');

        }elseif($activesubscription)
        {
            $activesubscription->decrement('remain_translation_limit');
        }


        return response()->json([
            "translation_id" => $translation->translation_id,
            'translatedText' => $translation->translated_text,

        ]);

    }
}
