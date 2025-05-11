<?php

namespace App\Http\Requests\Transcirption;

use Illuminate\Foundation\Http\FormRequest;

class TranscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'screen_id' => 'required|integer',
            'audio' => 'required|file|mimes:mp3,wav,ogg,flac,aac,alac,webm,mp4|max:5120', // max 5MB
        ];
    }
}
