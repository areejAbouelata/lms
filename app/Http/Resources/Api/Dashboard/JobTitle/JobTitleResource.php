<?php

namespace App\Http\Resources\Api\Dashboard\JobTitle;

use Illuminate\Http\Resources\Json\JsonResource;

class JobTitleResource extends JsonResource
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
            $locales[$locale]['title'] = $this->translate($locale)->title;
        }
        return [
                'id' => $this->id,
                'title' => $this->title,
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            ] + $locales;
    }
}
