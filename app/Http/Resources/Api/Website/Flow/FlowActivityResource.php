<?php

namespace App\Http\Resources\Api\Website\Flow;

use App\Http\Resources\Api\Website\Activity\ActivityResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FlowActivityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'desc' => $this->desc,
            'is_active' => $this->is_active,
            'activities' => ActivityResource::collection($this->activities), 
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ];
    }
}
