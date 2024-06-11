<?php

namespace App\Http\Controllers\Api\Dashboard\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Profile\{ProfileRequest, UpdatePasswordRequest};
use App\Http\Resources\Api\Dashboard\Auth\AdminResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function profile()
    {
        return (new AdminResource(auth('api')->user()))->additional(['status' => 'success', 'message' => '']);
    }

    public function update(ProfileRequest $request)
    {
        $user = auth('api')->user();
        $user->update($request->validated() + ['full_name' => request()->first_name . " " . request()->last_name]);
        return (new AdminResource(auth('api')->user()))->additional(['status' => 'success', 'message' => trans('dashboard.messages.success_update')]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth('api')->user();
        $user->update(['password' => $request->new_password]);
        return (new AdminResource($user->fresh()))->additional([
            'status' => 'success', 'message' => trans('dashboard.messages.success_change_password')
        ]);
    }
}
