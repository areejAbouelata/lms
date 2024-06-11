<?php

namespace App\Http\Requests\Api\Website\Assessment;

use Illuminate\Foundation\Http\FormRequest;

class FillBlankAnswerRequest extends FormRequest
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
        return [
            "answer" => "required|string" ,
            'question_id' => "required|exists:questions,id" ,
        ];
    }
}
