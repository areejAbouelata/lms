<?php

namespace App\Http\Requests\Api\Website\Auth;

use App\Http\Requests\Api\ApiMasterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends ApiMasterRequest
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
            'email' => [
                'required',
                function ($attribute, $value, $fail) {
                    $user = User::whereIn('user_type', [ 'client'])->where('email', $this->email)->first();
                    if (!$user){
                        $fail(trans("validation.invalid_login"));
                    }else{
                        if(!Hash::check($this->password, $user->password)){
                            $fail(trans("validation.invalid_login"));
                        }
                    }
                }
            ],
            'password' => [
                'required',
            ]
        ];
    }
}
