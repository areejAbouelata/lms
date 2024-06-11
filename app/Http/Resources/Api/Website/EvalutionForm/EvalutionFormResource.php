<?php

namespace App\Http\Resources\Api\Website\EvalutionForm;

use Illuminate\Http\Resources\Json\JsonResource;

class EvalutionFormResource extends JsonResource
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
            'id' => $this->id,
            'evalution_question' => $this->evalution_question,
            'is_active' => (bool)$this->is_active,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
        ] ;
    }
}
