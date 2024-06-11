<?php

namespace App\Http\Controllers\Api\Dashboard\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Auth\ForgetPasswordRequest;
use App\Http\Requests\Api\Dashboard\Auth\LoginRequest;
use App\Http\Requests\Api\Dashboard\Auth\SendCodeRequest;
use App\Http\Resources\Api\Dashboard\Auth\AdminResource;
use App\Mail\ForgetPassword;
use App\Models\User;
use App\Services\PhoneNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $token = auth('api')->attempt($request->validated());
        if (!$token) return response()->json(['data' => null, 'status' => 'failed', 'message' => trans('dashboard/admin.auth.failed_try_again')], 403);
        $user = auth('api')->user();

        if (!$user->is_active) {
            auth('api')->logout();
            return response()->json(['data' => null, 'status' => 'failed', 'message' => trans('dashboard/admin.auth.not_active')], 403);
        }

         $user->update(['last_login' => now()]); // TODO check about last login

        data_set($user, 'token', $token);
        return (new AdminResource($user))->additional(['status' => 'success', 'message' =>  trans('dashboard/admin.auth.success_login')]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard/admin.auth.success_logout')]);
    }
    public function sendCode(SendCodeRequest $request)
    {
//        TODO send email to user by link
        $user = \App\Models\User::where('email', $request->email)->firstOrFail();
        $code = PhoneNumberService::generateRandomString();
        $user->update(["forget_password_code" => $code]);
        $data['url'] = "https://onboarding.gulf-banquemisr.ae/auth/admin/reset-password?code=" . $code;
        Mail::to($user->email)->send(new ForgetPassword($data));
        return response()->json(['status' => 'success', 'data' => null, 'message' => trans('dashboard/admin.auth.success_sent')]);
    }

    public function forgetPassword(ForgetPasswordRequest $request)
    {
        $user = User::where(["forget_password_code" => $request->code])->firstOrFail();
        $user->update([
            'password' => $request->password, 'forget_password_code' => null
        ]);
        return response()->json(['status' => 'success', 'data' => null, 'message' => trans('dashboard/admin.auth.success_sent')]);
    }
}
