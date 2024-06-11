<?php

namespace App\Http\Requests\Api\Dashboard\Role;


use App\Http\Requests\Api\ApiMasterRequest;

class RoleRequest extends ApiMasterRequest
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
        $rules = [
          "permission_ids" =>"required|array" ,
          "permission_ids.*"  => "exists:permissions,id"
        ];
        foreach (config('translatable.locales') as $locale) {
             $rules[$locale . '.name'] = 'required|string|between:3,250|unique:role_translations,name,' . $this->role . ',role_id';
        }
        return $rules;
    }
}
