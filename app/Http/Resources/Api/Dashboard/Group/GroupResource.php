<?php

namespace App\Http\Resources\Api\Dashboard\Group;

use App\Http\Resources\Api\Dashboard\Client\ClientResource;
use App\Http\Resources\Api\Dashboard\Client\SimpleClientResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
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
            $locales[$locale]['name'] = $this->translate($locale)?->name;
            $locales[$locale]['desc'] = $this->translate($locale)?->desc;
        }

        return [
                'id' => (int)$this->id,
                'image' => (string)$this->image,
                'is_active' => (bool)$this->is_active,
                'admin' => ClientResource::make($this->admin),
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
                "users" => SimpleClientResource::collection($this->users),
                "enrollments" => $this->enrollment, //TODO
                "completions" => $this->completion, //TODO
                "name" => $this->name, //TODO
            ] + $locales;
    }
}
