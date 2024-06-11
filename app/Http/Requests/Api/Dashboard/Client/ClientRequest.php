<?php

namespace App\Http\Requests\Api\Dashboard\Client;

use App\Http\Requests\Api\ApiMasterRequest;

class ClientRequest extends ApiMasterRequest
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
        $user = $this->client ? $this->client : null;
        $status = $user ? "nullable" : "required";
        return [
            "image" => "nullable|string",
            "full_name" => "required|string|alpha_num:ascii",
            "first_name" => "required|string",
            "last_name" => "required|string",
            "phone" => "required_with:phone_code|nullable|numeric|unique:users,phone," . $user,
            "phone_code" => "required_with:phone|nullable",
            "email" => "required|email|unique:users,email," . $user,
            "gender" => "nullable|in:male,female",
            "is_active" => "nullable|in:0,1",
            "password" => $status,
//            "job_title_id" => "required|exists:job_titles,id",
            "job_title" => "required|string",
            "department_id" => "required|exists:departments,id",
            "group_id" => "required|exists:groups,id",
            "country_id" => "required|exists:countries,id",
            "nationality_id" => "nullable|exists:nationalities,id",
            "hire_date" => "nullable|date",
            "direct_manager" => "nullable|string",
            "age" => "nullable|numeric",
            "address" => "nullable|string",
            "mobile_code" => "nullable|string",
            "mobile" => "nullable|string"
        ];
    }
}
