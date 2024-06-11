<?php

namespace App\Http\Controllers\Api\Dashboard\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Dashboard\Permission\{PermissionRequest, SinglePermissionRequest};
use App\Models\Permission;
use App\Http\Resources\Api\Dashboard\Permission\{PermissionResource};

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $permissions = Permission::get() ;
        return  PermissionResource::collection($permissions)->additional([
            "status"        => "success",
            "message"       => ""
        ]);
    }

    public function show(Permission $permission)
    {
        return PermissionResource::make($permission)->additional(["status" => "success", "message" => ""]);
    }

    public function store(PermissionRequest $request)
    {

        $titles = (array) $request->titles;
        //  return $titles[1] ;
        for ($i = 0; $i < count($request->front_route_names); $i++) {
            Permission::create([
                'front_route_name'  =>  $request->front_route_names[$i],
                'back_route_name'  =>  $request->back_route_names[$i],
                'icon'  =>  $request->icons[$i],
                'ar'  =>  $request->titles[$i]["ar"],
                'en'  =>  $request->titles[$i]["en"],
            ]);
        }
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard/admin.Permission.created')]);
    }
    public function update(SinglePermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated()) ;
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard/admin.Permission.updated')]);

    }
    public function destroy(Permission $permission)
    {
        if ($permission->delete()) {
            return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard/admin.Permission.delete')]);
        }
    }
    public function indexNotPaginated(Request $request)
    {
        $Permission = Permission::latest()->get();
        return PermissionResource::collection($Permission)->additional([
            'message' => '',
            'status' =>  'success'
        ]);
    }
}
