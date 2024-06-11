<?php

namespace App\Http\Resources\Api\Dashboard\Slider;

use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
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
            'image' => $this->image,
            "id" => $this->id,
            "is_active" => $this->is_active,
            "user_type" => $this->user_type,
        ];
    }
}
