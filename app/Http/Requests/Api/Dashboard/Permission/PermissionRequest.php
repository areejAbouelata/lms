<?php

namespace App\Http\Requests\Api\Dashboard\Permission;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
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
            "back_route_names"               => "required|array",
            "back_route_names.*"               => "string",
            "front_route_names"               => "required|array",
            "front_route_names.*"               => "string",
            "titles"                        => "required|array",
            "icons"                        => "required|array",

        ];
        // foreach ($this->titles as $title) {
        //     foreach (config('translatable.locales') as $locale) {
        //         $rules[$locale . '.title'] = 'required|string|between:3,250';
        //     }
        // }
        return $rules;
    }
}
