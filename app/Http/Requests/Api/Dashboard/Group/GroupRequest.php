<?php

namespace App\Http\Requests\Api\Dashboard\Group;

use App\Http\Requests\Api\ApiMasterRequest;

class GroupRequest extends ApiMasterRequest
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
        $status = isset($this->group) ? 'nullable' : 'required';

        $rules = [
            'image' => 'nullable|string',
            'is_active' => $status . '|in:0,1',
            'admin_id' => 'required|exists:users,id',
            "user_ids" => "nullable|array" ,
            "user_ids.*" => "exists:users,id"
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name'] = $status . '|string|between:2,250';
            $rules[$locale . '.desc'] = 'nullable|string|between:2,10000';
        }

        return $rules;
    }
}
