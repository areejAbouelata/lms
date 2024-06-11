<?php

namespace App\Http\Requests\Api\Website\EvalutionForm;

use App\Http\Requests\Api\ApiMasterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
        return [
           'flow_id'                                     => 'required|exists:flows,id' ,
           'rate'                                        => 'required',
           'evalution_answers.*'                         => 'nullable|array',
           'evalution_answers.*.evalution_form_id'       => 'nullable|exists:evalution_forms,id' ,
           'evalution_answers.*.evalution_form_answer'   => 'nullable|string' ,
        ];
    }
}
