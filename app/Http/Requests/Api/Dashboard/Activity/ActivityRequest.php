<?php

namespace App\Http\Requests\Api\Dashboard\Activity;

use Illuminate\Foundation\Http\FormRequest;

class ActivityRequest extends FormRequest
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
        $activity = $this->activity ? $this->activity->id : null;
        $rules = [
//            "image" => "required_if:type,video,audio,assessment,html_content|string",

            "image" => "nullable|string",
            "flow_id" => "required:exists:flows,id",
            'type' => "required|in:task,video,audio,assessment,html_content",
            'has_attachment' => 'required_if:type,task|in:0,1',
//            'max_trails' => 'required_if:type,assessment|numeric',
            "assessment_type" => "required_if:type,assessment|in:fill_blank,choice,drag_drop",
            "duration_type" => "required|in:day,hour,month",
            "duration" => "required|numeric",
            "time_minutes" => "nullable|numeric",
            "is_active" => "nullable|in:0,1",
//            "step_number" => "required|numeric|unique:activities,step_number,' . $this->flow_id . ',flow_id",
            // ++++++++++++++++ assessment data
            "question" => "required_if:type,assessment|string",
            "answer" => "required_if:type,assessment|array",
            "answer.*" => "string",
            "match_answer" => "required_if:assessment_type,drag_drop|array",
            "match_answer.*" => "string",
            "is_correct_answer" => "required_if:type,assessment|array",
            "is_correct_answer.*" => "required|in:0,1",
            "attachment" => "required_unless:type,assessment,type,task|array",
            "attachment.*" => "string",
            "attachment_type" => "required_unless:type,assessment,type,task|array",
            "attachment_type.*" => "in:files,images",
            "note_question" => "nullable|string" ,
            "has_note" => "nullable"
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.desc'] = 'required|string';
//            $rules[][$locale . '.answer.*'] = 'nullable|string|between:2,250';
        }
        return $rules;
    }
}
