<?php

namespace App\Http\Resources\Api\Dashboard\Admin\Country;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryListResource extends JsonResource
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
                'id'                      => $this->id,
                'name'                    => $this->name ,
                'short_name'              => $this->short_name,
                'phone_limit'             => (int)$this->phone_limit,
                'image'                   => $this->image,

            ];
        
    }
}
