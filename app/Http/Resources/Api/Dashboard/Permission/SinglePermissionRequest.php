<?php

namespace App\Http\Requests\Api\Dashboard\Permission;

use Illuminate\Foundation\Http\FormRequest;

class SinglePermissionRequest extends FormRequest
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
        $rules = [];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.title'] = 'required|string|between:3,250';
        }
        return [
            "back_route_name"               => "required|string",
            "front_route_name"               => "required|string",
            "icon"                        => "nullable",
        ] + $rules;
    }
}
