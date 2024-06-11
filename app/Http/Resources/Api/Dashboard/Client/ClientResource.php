<?php

namespace App\Http\Resources\Api\Dashboard\Client;

use App\Http\Resources\Api\Dashboard\Country\CountryResource;
use App\Http\Resources\Api\Dashboard\Department\SimpleDepartmentResource;
use App\Http\Resources\Api\Dashboard\Flow\FlowResource;
use App\Http\Resources\Api\Dashboard\Group\GroupSimpleResource;
use App\Http\Resources\Api\Dashboard\JobTitle\SimpleJobTitleResource;
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
        $client_data = [];
        if ($this->user_type == 'client') {
            $client_data['direct_manager'] = $this->direct_manager;
            $client_data['nationality'] = $this->nationality?->translate($locale)->name;
            $client_data['age'] = $this->age;
            $client_data['address'] = $this->address;
            $client_data['hire_date'] = $this->hire_date;
            $client_data['mobile_code'] = $this->mobile_code;
            $client_data['mobile'] = $this->mobile;
        }
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
                "job_title" => $this->job_title ?? '',
//            "job_title" => SimpleJobTitleResource::make($this->jobTitile),
                "department" => SimpleDepartmentResource::make($this->department),
                "group" => GroupSimpleResource::make($this->group),
                "country" => CountryResource::make($this->country),
                'user_completion' => $this->completion ?? 0,

//            flow
                "flows" => FlowResource::collection($this->flows),
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            ] + $client_data;
    }
}
