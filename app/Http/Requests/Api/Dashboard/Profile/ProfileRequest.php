<?php

namespace App\Http\Requests\Api\Dashboard\Profile;

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
        $status = request()->method() == 'PUT' ? 'nullable' : 'required';
        return [
            "image" => "nullable|string",
            "full_name" => "required|string",
            "phone" => "$status|numeric|unique:users,phone," . auth('api')->id(),
            'email' => 'required|email|unique:users,email,' . auth('api')->id(),
        ];
    }
}
