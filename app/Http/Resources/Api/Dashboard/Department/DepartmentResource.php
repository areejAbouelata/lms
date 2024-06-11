<?php

namespace App\Http\Resources\Api\Dashboard\Department;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
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
                'desc' => $this->desc,
                'is_active' => (bool)$this->is_active,
                'users' => $this->users()->count(),
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            ] + $locales;
    }
}
