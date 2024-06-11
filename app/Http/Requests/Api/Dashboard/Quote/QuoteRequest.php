<?php

namespace App\Http\Requests\Api\Dashboard\Quote;

use Illuminate\Foundation\Http\FormRequest;

class QuoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            "author" => "required|string",
            "author_job" => "required|string",
            "image" => "required|string",
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.title'] = 'required|string|between:3,250';
            $rules[$locale . '.desc'] = 'required|string|between:2,250';
        }
        return $rules;
    }
}
