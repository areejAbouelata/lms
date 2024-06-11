<?php

namespace App\Http\Resources\Api\Website\Activity;

use App\Models\UserQuestionAnswer;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'assessment_type' => $this->assessment_type,
            $this->mergeWhen($this->assessment_type == 'drag_drop', [
                'arranged_assessments' => AssessmentResource::collection($this->assessments()->where('match_answer_id', null)->get()),
                "not_arranged_assessments" => AssessmentResource::collection($this->assessments()->whereNotNull('match_answer_id')->inRandomOrder()->get())
            ]),
            $this->mergeWhen($this->assessment_type == 'choice', [
                'assessments' => AssessmentResource::collection($this->assessments) ,
                "single_choice"  =>$this->assessments()->where('is_correct_answer' , true)->count() == 1 ? true :false
            ])
            ,
            "trails" => (int)UserQuestionAnswer::where(["user_id" => auth('api')->id(), 'question_id' => $this->id])->first()?->trails,
            "is_answered" => (int)UserQuestionAnswer::where(["user_id" => auth('api')->id(), 'question_id' => $this->id])->first()?->is_answered
        ];
    }
}
