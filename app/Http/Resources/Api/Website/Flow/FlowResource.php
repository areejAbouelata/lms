<?php

namespace App\Http\Resources\Api\Website\Flow;

use Illuminate\Http\Resources\Json\JsonResource;

class FlowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'desc' => $this->desc,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,

        ];
    }
}
