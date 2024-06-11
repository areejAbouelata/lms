<?php

namespace App\Http\Requests\Api\Dashboard\Admin;

use App\Http\Requests\Api\ApiMasterRequest;

class AdminRequest extends ApiMasterRequest
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
        $user = $this->admin ? $this->admin : null;

        return [
            "image" => "nullable|string",
            "first_name" => "required|string",
            "last_name" => "required|string",
//            "phone" => "nullable|numeric|unique:users,phone," . $user,
            "email" => "required|email|unique:users,email," . $user,
//            "gender" => "nullable|in:male,female",
            "is_active" => "nullable|in:0,1",
            "password" => "required",
            "role_id" => "required|exists:roles,id",
        ];
    }
}
