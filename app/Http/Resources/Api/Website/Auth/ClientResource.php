<?php

namespace App\Http\Resources\Api\Website\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $locale = app()->getLocale();
        return [
            'id' => (int)$this->id,
            'full_name' => (string)$this->full_name,
            'first_name' => (string)$this->first_name,
            'last_name' => (string)$this->last_name,
            'image' => (string)$this->image,
            'phone' => (string)$this->phone,
            'email' => (string)$this->email,
            'user_type' => (string)$this->user_type,
            'gender' => (string)$this->gender,
            'is_active' => (bool)$this->is_active,
            'direct_manager' => $this->direct_manager,
            'nationality' => $this->nationality?->translate($locale)->name,
            'age' => $this->age,
            'address' => $this->address,
            'hire_date' => $this->hire_date,
            'mobile_code' => $this->mobile_code,
            'mobile' => $this->mobile,
//            "job_title" => SimpleJobTitleResource::make($this->jobTitile),
            "job_title" => $this->job_title,
            "last_login" => $this->last_login,
            "department" => SimpleDepartmentResource::make($this->department),
            "group" => GroupSimpleResource::make($this->group),
            "country" => CountryResource::make($this->country),
            'token' => $this->when($this->token, $this->token),
            'first_login' => $this->first_login == $this->last_login ?  true : false,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ];
    }
}
