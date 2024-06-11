<?php

namespace App\Http\Resources\Api\Dashboard\Permission;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $locales = [];
        foreach (config('translatable.locales') as $locale) {
            $locales[$locale]['title'] = $this->translate($locale)?->title;
        }
        return[
            "id"    => $this->id ,
            "url"       => $this->front_route_name ,
            "back_route_name"    => $this->back_route_name ,
            "icon"    => $this->icon ,
            "is_control_permission"    => $this->is_control_permission ,
        ]+$locales;
    }
}
