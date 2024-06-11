<?php

namespace App\Http\Resources\Api\Dashboard\Nationality;

use Illuminate\Http\Resources\Json\JsonResource;

class NationalityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $locale = app()->getLocale();
        return [
            'id' => (int)$this->id,
            'name' => $this->name
        ];
    }
}
