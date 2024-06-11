<?php

namespace App\Http\Controllers\Api\Dashboard\JobTitle;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\JobTitle\JobTitleRequest;
use App\Http\Resources\Api\Dashboard\JobTitle\JobTitleResource;
use App\Models\JobTitle;
use Illuminate\Http\Request;

class JobTitleController extends Controller
{
    public function index(Request $request)
    {
        $job_title = JobTitle::latest()->paginate(25);
        return JobTitleResource::collection($job_title)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function show(JobTitle $job_title)
    {
        return JobTitleResource::make($job_title)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function indexWithoutPagination(Request $request)
    {
        $job_title = JobTitle::latest()->get();
        return JobTitleResource::collection($job_title)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function store(JobTitleRequest $request)
    {
        $job_title = JobTitle::create($request->validated());
        return JobTitleResource::make($job_title)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function update(JobTitleRequest $request, JobTitle $job_title)
    {
        $job_title->update($request->validated());
        return JobTitleResource::make($job_title)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function destroy(JobTitle $job_title)
    {
        $job_title->delete();
        return response()->json([
            "data" => null,
            'status' => "success",
            "message" => ""
        ]);
    }
}
