<?php

namespace App\Http\Requests\Api\Website\Profile;

use App\Http\Requests\Api\ApiMasterRequest;

class ProfileRequest extends ApiMasterRequest
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
            "image" => "nullable|string",
            "full_name" => "nullable|string",
//            "last_name" => "required|string",
            "phone" => "nullable|numeric|unique:users,phone," . auth('api')->id(),
            'email' => 'nullable|email|unique:users,email,' . auth('api')->id(),
//            "job_title_id" => "required|exists:job_titles,id",
            "job_title" => "nullable|string",
            "department_id" => "nullable|exists:departments,id",
            "group_id" => "nullable|exists:groups,id",
            "country_id" => "nullable|exists:countries,id",
        ];
    }
}
