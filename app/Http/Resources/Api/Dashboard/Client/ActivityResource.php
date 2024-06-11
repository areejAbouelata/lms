<?php

namespace App\Http\Resources\Api\Dashboard\Client;

use App\Http\Resources\Api\Dashboard\Activity\QuestionResource;
use App\Http\Resources\Api\Dashboard\Flow\FlowResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $has_attachment = isset($this->has_attachment) ? ($this->has_attachment) == 1 ? true : false : false;
        return [
            "id" => $this->id,
            "image" => $this->image,
            "flow" => FlowResource::make($this->flow), // resource
            "type" => $this->type,
            "max_trails" => $this->max_trails,
            "assessment_type" => $this->assessment_type,
            "duration_type" => $this->duration_type,
            "duration" => $this->duration,
            "is_active" => (bool)$this->is_active,
            "step_number" => $this->step_number,
            "answer" => $this->assessment?->answer,
            "desc" => $this->desc,
            "attachment" => $this->attachmentAttribute(),
            "questions" => \App\Http\Resources\Api\Dashboard\Client\QuestionResource::collection($this->questions),
            "has_attachment" => $has_attachment ,
        ];
    }
}
