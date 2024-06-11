<?php

namespace App\Http\Requests\Api\Dashboard\Profile;

use App\Http\Requests\Api\ApiMasterRequest;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordRequest extends ApiMasterRequest
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
            "current_password" => ["required", "min:6", function ($attribute, $value, $fail) {
                if (! Hash::check($value, auth('api')->user()->password)) {
                    $fail(trans('dashboard.messages.current_passowrd_incorrect'));
                }
            }],
            "new_password" => "required|min:6"
        ];
    }
}
