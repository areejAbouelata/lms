<?php

namespace App\Http\Controllers\Api\Website\Flow;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Website\Flow\FlowActivityResource;
use App\Http\Resources\Api\Website\Flow\FlowResource;
use Illuminate\Http\Request;

class FlowController extends Controller
{
    public function flows()
    {
        return FlowResource::collection( auth('api')->user()->flows)->additional([
            'status' => "success" ,
            "message" => ""
        ]) ;
    }

    public function show($id)
    {
        $flow =  auth('api')->user()->flows()->findOrFail($id);

        return FlowActivityResource::make($flow)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }
}
