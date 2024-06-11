<?php

namespace App\Http\Controllers\Api\Dashboard\Enroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Enroll\EnrollRequest;
use App\Models\Activity;
use App\Models\ActivityUser;
use App\Models\User;
use Illuminate\Http\Request;

class EnrollController extends Controller
{
    public function __invoke(EnrollRequest $request)
    {
//        flow_id
        $activities = Activity::where('flow_id', $request->flow_id)->get();

//        user_ids
        foreach (User::whereIn('id', $request->user_ids)->get() as $user) {
            foreach ($activities as $activity) {
                ActivityUser::create([
                    'user_id' => $user->id,
                    "activity_id" => $activity->id
                ]);
            }
        }
        return response()->json([
            "data" => null,
            'status' => "success",
            "message" => ""
        ]);
    }
}
