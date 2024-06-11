<?php

namespace App\Http\Resources\Api\Dashboard\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class SimpleClientResource extends JsonResource
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
            'full_name'  => (string) $this->full_name,
            'first_name' => (string) $this->first_name,
            'last_name'  => (string) $this->last_name,
            'image'      => (string) $this->image,
            'phone'      => (string) $this->phone,
            'email'      => (string) $this->email,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ];
    }
}
