<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlanRequest extends FormRequest
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
            "plan_name" => "required|string|max:255",
            "plan_price" => "required|numeric",
            "plan_period" => "required|string|max:255",
            "plan_description" => "required|string|max:255",
            "plan_transcription_limit" => "required|integer",
            "plan_translation_limit" => "required|integer",
            "plan_price_cents" => "required|integer",
        ];
    }
}
