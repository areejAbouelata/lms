<?php

namespace App\Http\Resources\Api\Dashboard\Flow;

use App\Http\Resources\Api\Dashboard\Client\SimpleClientResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterFlowResource extends JsonResource
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
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
                "users" => SimpleClientResource::collection($this->users()->take(5)->distinct()->get())
            ] + $locales;
    }
}
