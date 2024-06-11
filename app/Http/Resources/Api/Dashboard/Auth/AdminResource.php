<?php

namespace App\Http\Resources\Api\Dashboard\Auth;

use App\Http\Resources\Api\Dashboard\Role\TranslatedRoleResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (int)$this->id,
            'full_name' => (string)$this->full_name,
            'first_name' => (string)$this->first_name,
            'last_name' => (string)$this->last_name,
            'image' => (string)$this->image,
//            'phone' => (string)$this->phone,
            'email' => (string)$this->email,
            'user_type' => (string)$this->user_type,
//            'gender' => (string)$this->gender,
            'is_active' => (bool)$this->is_active,
            'token' => $this->when($this->token, $this->token),
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            "role" => TranslatedRoleResource::make($this->role)
        ];
    }
}
