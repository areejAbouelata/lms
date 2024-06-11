<?php

namespace App\Http\Controllers\Api\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Admin\AdminRequest;
use App\Http\Resources\Api\Dashboard\Auth\AdminResource;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admins = User::where("user_type", "admin")
            ->where("id", "!=", auth('api')->id())
            ->when(request()->keyword, function ($query) {
                $query->where(function ($query) {
                    $query->where('full_name', 'LIKE', '%' . request()->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . request()->keyword . '%');
                });
            })->latest()->when(is_numeric(request()->paginate), function ($query) {
                return $query->paginate(request()->paginate);
            }, function ($query) {
                return $query->get();
            });

        return AdminResource::collection($admins)->additional([
            "status" => "success",
            "message" => "",
        ]);
    }

    public function indexWithoutPagination()
    {
        $admins = User::where("user_type", "admin")
            ->where("id", "!=", auth('api')->id())
            ->when(request()->keyword, function ($query) {
                $query->where(function ($query) {
                    $query->where('full_name', 'LIKE', '%' . request()->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . request()->keyword . '%');
                });
            })
            ->where('is_active' , true)
            ->latest()->get();

        return AdminResource::collection($admins)->additional([
            "status" => "success",
            "message" => "",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminRequest $request)
    {
        $admin = User::create($request->validated() + ["user_type" => "admin", "is_active" => true, 'full_name' => request()->first_name . " " . request()->last_name]);
        return AdminResource::make($admin)->additional(["status" => "success", 'message' => trans('dashboard/admin.admin.created')]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $admin = User::where("user_type", "admin")->where("id", "!=", auth('api')->id())->findOrFail($id);
        return (new AdminResource($admin))->additional(["status" => "success", "message" => ""]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(AdminRequest $request, $id)
    {
        $admin = User::where("user_type", "admin")->where("id", "!=", auth('api')->id())->findOrFail($id);
        $admin->update($request->validated() + ["user_type" => "admin", 'full_name' => request()->first_name . " " . request()->last_name]);

        return (new AdminResource($admin->fresh()))->additional([
            "status" => "success",
            "message" => trans('dashboard/admin.admin.updated'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where("user_type", "admin")->where("id", "!=", auth('api')->id())->findOrFail($id);
        if ($user->delete()) {
            return response()->json(['status' => 'success', 'data' => null, 'message' => trans('dashboard/admin.admin.deleted')]);
        }
    }

    public function toggleActive($id)
    {
        $admin = User::where("user_type", "admin")->where("id", "!=", auth('api')->id())->findOrFail($id);
        $admin->update(['is_active' => request()->is_active]);
        return (new AdminResource($admin))->additional([
            "status" => "success",
            "message" => trans('dashboard/admin.admin.updated'),
        ]);
    }
}
