<?php

namespace App\Http\Resources\Api\Dashboard\Activity;

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
            'assessments' => AssessmentResource::collection($this->assessments)
        ];
    }
}
