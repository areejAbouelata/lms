<?php

namespace App\Http\Resources\Api\Dashboard\Activity;

use App\Http\Resources\Api\Dashboard\Flow\FlowResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $has_attachment = isset($this->has_attachment) ? ($this->has_attachment) == 1 ? true : false : false;
        return [
            "id" => $this->id,
            "image" => $this->image,
            "flow" => FlowResource::make($this->flow), // resource
            "type" => $this->type,
//            "max_trails" => $this->max_trails,
            "assessment_type" => $this->assessment_type,
            "duration_type" => $this->duration_type,
            "duration" => $this->duration,
            "time_minutes" => $this->time_minutes,
            "is_active" => (bool)$this->is_active,
            "step_number" => $this->step_number,
            "answer" => $this->assessment?->answer,
            "desc" => $this->desc,
            "attachment" => $this->attachmentAttribute(),

//            'assessments' => AssessmentResource::collection($this->assessments)
            "questions" => QuestionResource::collection($this->questions),
            "has_attachment" => $has_attachment
        ];
    }
}
