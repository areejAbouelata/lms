<?php

namespace App\Http\Resources\Api\Dashboard\EvalutionForm;

use Illuminate\Http\Resources\Json\JsonResource;

class EvalutionFormResource extends JsonResource
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
        $current_locale = app()->getLocale();
        foreach (config('translatable.locales') as $locale) {
            $locales[$locale]['evalution_question'] = $this->translate($locale)->evalution_question;
        }
        return [
                'id' => $this->id,
                'evalution_question' => $this->translate($current_locale)->evalution_question,
                'is_active' => (bool)$this->is_active,
                'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            ] + $locales;

    }
}
