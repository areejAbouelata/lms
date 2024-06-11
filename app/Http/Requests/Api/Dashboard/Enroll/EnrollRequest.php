<?php

namespace App\Http\Requests\Api\Dashboard\Enroll;

use Illuminate\Foundation\Http\FormRequest;

class EnrollRequest extends FormRequest
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
            "user_ids" => "required|array",
            "user_ids.*" => "exists:users,id",
            "flow_id" => "required|exists:flows,id"
        ];
    }
}
