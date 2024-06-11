<?php

namespace App\Http\Controllers\Api\Website\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Website\Profile\ProfileRequest;
use App\Http\Requests\Api\Website\Profile\UpdatePasswordRequest;
use App\Http\Resources\Api\Website\Auth\ClientResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function profile()
    {
        return (new ClientResource(auth('api')->user()))->additional(['status' => 'success', 'message' => '']);
    }

    public function update(ProfileRequest $request)
    {
        $user = auth('api')->user();
        $user->update($request->validated() + ['full_name' => request()->first_name . " " . request()->last_name]);
        return (new ClientResource(auth('api')->user()))->additional(['status' => 'success', 'message' => '']);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth('api')->user();
        $user->update(['password' => $request->new_password]);
        return (new ClientResource($user->fresh()))->additional([
            'status' => 'success', 'message' => trans('dashboard.messages.success_change_password')
        ]);
    }
}
