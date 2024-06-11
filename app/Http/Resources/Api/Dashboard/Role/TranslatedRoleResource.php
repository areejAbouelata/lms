<?php

namespace App\Http\Resources\Api\Dashboard\Role;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\Dashboard\Permission\PermissionResource;

class TranslatedRoleResource extends JsonResource
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
            $locales[$locale]['name'] = $this->translate($locale) ?->name;
        }
        return [
            "id"            =>  $this->id,
            "permission"       => PermissionResource::collection($this->permissions)
        ] + $locales;
    }
}
