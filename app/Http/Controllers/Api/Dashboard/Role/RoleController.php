<?php

namespace App\Http\Controllers\Api\Dashboard\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Role\{RoleRequest};
use App\Http\Resources\Api\Dashboard\Role\{RoleResource, TranslatedRoleResource};
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $role = Role::latest()->paginate(25);
        return RoleResource::collection($role)->additional([
            'message' => '',
            'status' => 'success'
        ]);
    }

    public function indexWithoutPagination(Request $request)
    {
        $role = Role::latest()->get();
        return RoleResource::collection($role)->additional([
            'message' => '',
            'status' => 'success'
        ]);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return TranslatedRoleResource::make($role)->additional(['status' => 'success', 'message' => '']);
    }

    public function indexNotPaginated(Request $request)
    {
        $role = Role::latest()->get();
        return RoleResource::collection($role)->additional([
            'message' => '',
            'status' => 'success'
        ]);
    }

    public function store(RoleRequest $request)
    {
        // return ($request->validated()) ;
        $role = Role::create(array_except($request->validated(), ["permission_ids"]));
        $role->permissions()->attach($request->permission_ids);
        return response()->json(['status' => 'success', 'data' => null, 'message' => trans('dashboard/admin.role.created')]);
    }

    public function update(RoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update(array_except($request->validated(), ["permission_ids"]));
        $role->permissions()->sync($request->permission_ids);
        return response()->json(['status' => 'success', 'data' => null, 'message' => trans('dashboard/admin.role.updated')]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if ($role->users()->count()) {
            return response()->json(['status' => 'success', 'data' => null, 'message' => trans('dashboard/admin.role.has_users')], 422);
        }
        $role->delete();
        return response()->json(['status' => 'success', 'data' => null, 'message' => trans('dashboard/admin.role.destroy')]);
    }
}
