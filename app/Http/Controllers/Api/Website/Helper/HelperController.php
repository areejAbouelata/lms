<?php

namespace App\Http\Controllers\Api\Website\Helper;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Dashboard\JobTitle\JobTitleResource;
use App\Http\Resources\Api\Website\Auth\CountryResource;
use App\Http\Resources\Api\Website\Auth\GroupSimpleResource;
use App\Http\Resources\Api\Website\Auth\SimpleDepartmentResource;
use App\Http\Resources\Api\Website\EvalutionForm\EvalutionFormResource;
use App\Models\{Country, Department, Group, JobTitle , EvalutionForm};
use Illuminate\Http\Request;

class HelperController extends Controller
{
    public function group(Request $request)
    {
        $group = Group::latest()->get();
        return GroupSimpleResource::collection($group)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function jobTitle(Request $request)
    {
        $job_title = JobTitle::latest()->get();
        return JobTitleResource::collection($job_title)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }


    public function country(Request $request)
    {
        $countries = Country::where('is_active', 1)->latest()->get();
        return CountryResource::collection($countries)->additional([
            'message' => '',
            'status' => 'success'
        ]);
    }

    public function department(Request $request)
    {
        $department = Department::where('is_active', 1)->paginate(25);
        return SimpleDepartmentResource::collection($department)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }
    
     public function evalution_form()
    {
        return EvalutionFormResource::collection(EvalutionForm::where('is_active', 1)->get())->additional([
            'status' => "success" ,
            "message" => ""
        ]) ;
    }
}
