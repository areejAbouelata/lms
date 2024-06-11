<?php

namespace App\Http\Resources\Api\Dashboard\Group;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupSimpleResource extends JsonResource
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
            'id'         => (int) $this->id,
            'image'      => (string) $this->image,
            'is_active'  => (bool) $this->is_active,
            'name'       => (string) $this->name,
            'desc'       => (string) $this->desc,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ];
    }
}
