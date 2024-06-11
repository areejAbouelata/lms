<?php

namespace App\Http\Resources\Api\Website\Activity;

use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return
            [
                'id' => $this->id,
                "answer" => $this->answer,
                "created_at" => $this->created_at
            ];
    }
}
