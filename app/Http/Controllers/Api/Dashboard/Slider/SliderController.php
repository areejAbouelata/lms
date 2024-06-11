<?php

namespace App\Http\Controllers\Api\Dashboard\Slider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Slider\SliderRequest;
use App\Http\Resources\Api\Dashboard\Slider\SliderResource;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        $Slider = Slider::latest()->paginate(25);
        return SliderResource::collection($Slider)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function show(Slider $slider)
    {
        return SliderResource::make($slider)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function indexWithoutPagination(Request $request)
    {
        $slider = Slider::latest()->get();
        return SliderResource::collection($slider)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function store(SliderRequest $request)
    {
        $request->is_active && Slider::where("user_type", $request->user_type)->count() ? Slider::where("user_type", $request->user_type)->update(['is_active' => false]) : null;
        $slider = Slider::create($request->validated());
        return SliderResource::make($slider)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function update(SliderRequest $request, Slider $slider)
    {
        $request->is_active && Slider::where("user_type", $request->user_type)->count() ? Slider::where("user_type", $request->user_type)->update(['is_active' => false]) : null;
        $slider->update($request->validated());
        return SliderResource::make($slider)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();
        return SliderResource::make($slider)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }
}
