<?php

namespace App\Http\Resources\Api\Dashboard\Department;

use Illuminate\Http\Resources\Json\JsonResource;

class SimpleDepartmentResource extends JsonResource
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
                'desc' => $this->desc,
            'is_active' => (bool)$this->is_active,

            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            ] ;
    }
}
