<?php

namespace App\Http\Controllers\Api\Website\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
// contact hr
    public function contact()
    {
        $settings = Setting::whereIn("key", [
            "email",
            "whatsapp",
            "mobile",
            "work_time_start",
            "work_time_end",
            "work_day_start",
            "work_day_end",
            "address",
            "lat",
            "lng",
            "location",
        ])->get();
        $data = [];
        foreach ($settings as $setting) {
            $data[$setting->key] = $setting->value;
        }
        return response()->json([
            'data' => $data,
            "status" => "success",
            "message" => ""
        ]);
    }

//get how it works
    public function howItWork()
    {
        $settings = Setting::whereIn("key", [
            "how_it_works"
        ])->get();
        $data = [];
        foreach ($settings as $setting) {
            $data[$setting->key] = $setting->how_it_works;
        }
        return response()->json([
            'data' => $data,
            "status" => "success",
            "message" => ""
        ]);
    }
}
