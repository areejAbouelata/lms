<?php

namespace App\Http\Resources\Api\Dashboard\Flow;

use Illuminate\Http\Resources\Json\JsonResource;

class FlowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $locales = [];
        foreach (config('translatable.locales') as $locale) {
            $locales[$locale]['name'] = $this->translate($locale)->name;
            $locales[$locale]['desc'] = $this->translate($locale)->desc;
        }
        return [
                'id' => $this->id,
                'name' => $this->name,
                'image' => $this->image,
                'desc' => $this->desc,
                'is_active' => $this->is_active,
                'completion' => $this->completion,
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            ] + $locales;
    }
}
