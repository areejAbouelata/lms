<?php

namespace App\Http\Resources\Api\Website\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class SimpleJobTitleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ];
    }
}
