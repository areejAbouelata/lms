<?php

namespace App\Http\Requests\Api\Dashboard\EvalutionForm;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Api\ApiMasterRequest;


class EvalutionFormRequest extends ApiMasterRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $form = $this->form ? $this->form->id : null;
        $rules = [
            'is_active'               => 'nullable|in:0,1',
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.evalution_question'] = 'required|string|between:2,250' ;

        }
        return $rules;
    }
}
