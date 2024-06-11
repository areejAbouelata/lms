<?php

namespace App\Http\Resources\Api\Dashboard\Client;

use App\Http\Resources\Api\Dashboard\Activity\AssessmentResource;
use App\Models\ActivityAnswerUser;
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
        $user_question_answer = UserQuestionAnswer::where(['question_id' => $this->id, "user_id" => $request->user_id])->get();
        $user_question_answers = [];
        foreach ($user_question_answer as $item) {
            $user_question_answers [] = ['is_correct' => $item->is_correct, 'trails' => $item->trails, 
            'answer' => ActivityAnswerUser::where(['user_id' => $item->user_id, 'question_id' => $this->id])->first()?->user_answer ,
            ];
        }
        return [
            'id' => $this->id,
            'question' => $this->question,
            'assessment_type' => $this->assessment_type,
            'assessments' => AssessmentResource::collection($this->assessments),
            "user_answers" => $user_question_answers,
        ];
    }
}
