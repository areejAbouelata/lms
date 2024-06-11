<?php

namespace App\Http\Resources\Api\Dashboard\Activity;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class AssessmentResource extends JsonResource
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
            'question_id' => $this->question?->id,
            'question' => $this->question?->question,
            "answer" => $this->answer,
            "is_correct_answer" => $this->is_correct_answer,
            "match_answer" => new  AssessmentResource($this->matchAnswer),
            "created_at" => $this->created_at
        ];
    }
}
