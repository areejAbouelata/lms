<?php

namespace App\Http\Requests\Api\Dashboard\Department;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
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

        $department = $this->department ? $this->department->id : null;
        $rules = [

        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name'] = 'required|string|between:3,250|unique:department_translations,name,' . $department . ',department_id';
            $rules[$locale . '.desc'] = 'required|string|between:2,250';
        }
        return $rules;
    }
}
