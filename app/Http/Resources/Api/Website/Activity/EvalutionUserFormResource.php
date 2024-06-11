<?php

namespace App\Http\Resources\Api\Website\Activity;

use App\Models\{UserQuestionAnswer,EvalutionForm};
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;
use App\Http\Resources\Api\Website\EvalutionForm\EvalutionFormResource;

class EvalutionUserFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'evalution_question'    => new EvalutionFormResource(EvalutionForm::where('id',$this->evalution_form_id)->first()),
            'answer'                => $this->evalution_form_answer ,
        ];
    }
}
