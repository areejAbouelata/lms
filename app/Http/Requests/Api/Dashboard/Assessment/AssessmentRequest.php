<?php

namespace App\Http\Requests\Api\Dashboard\Assessment;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentRequest extends FormRequest
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
            "assessment_type" => "required_if:type,assessment|in:fill_blank,choice,drag_drop",
            "activity_id" => "required|exists:activities,id",
            "question" => "required_if:type,assessment|string",
            "answer" => "required_if:type,assessment|array",
            "answer.*" => "string",
            "match_answer" => "required_if:assessment_type,drag_drop|array",
            "match_answer.*" => "string",
            "is_correct_answer" => "required_if:type,assessment|array",
            "is_correct_answer.*" => "required|in:0,1",
        ];
    }
}
