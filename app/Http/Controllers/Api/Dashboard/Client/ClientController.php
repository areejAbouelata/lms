<?php

namespace App\Http\Controllers\Api\Dashboard\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Client\ClientRequest;
use App\Http\Resources\Api\Dashboard\Client\ClientActivitiesByFlowResource;
use App\Http\Resources\Api\Dashboard\Client\ClientResource;
use App\Http\Resources\Api\Dashboard\Client\UserActivities;
use App\Http\Services\ClientServices;
use App\Models\{ActivityUser, Flow};
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public $services;

    public function __construct(ClientServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {
        $clients = $this->services->getClients();
        return ClientResource::collection($clients)->additional(["status" => "success", "message" => ""]);
    }

    public function indexWithoutPagination()
    {
        $clients = User::where("user_type", "client")
            ->when(request()->keyword, function ($query) {
                $query->where(function ($query) {
                    $query->where('full_name', 'LIKE', '%' . request()->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . request()->keyword . '%');
                });
            })->when(request()->group_id, function ($query) {
                $query->where('group_id', request()->group_id);
            })->when(request()->department_id, function ($query) {
                $query->where('department_id', request()->department_id);
            })->latest()->get();
        return ClientResource::collection($clients)->additional(["status" => "success", "message" => ""]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(ClientRequest $request)
    {
        $client = User::create($request->validated() + ["user_type" => "client", 'full_name' => request()->first_name . " " . request()->last_name]);
//        TODO SEND EMAIL TO USER CONTAIN PASSWORD AND EMAIl
        $this->services->sendMail($request);
        return ClientResource::make($client)->additional(["status" => "success", 'message' => trans('dashboard.messages.user_created_successfully')]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $client = User::where("user_type", "client")->findOrFail($id);
        return (new ClientResource($client))->additional(["status" => "success", "message" => ""]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $client = User::where("user_type", "client")->findOrFail($id);
        if ($client->delete()) {
            return response()->json(['status' => 'success', 'data' => null, 'message' => trans('dashboard/admin.admin.deleted')]);
        }
    }

    public function toggleActive(Request $request)
    {
        User::where("user_type", "client")->whereIn("id", $request->ids)->update(['is_active' => request()->is_active]);
        return response()->json(["status" => "success", "message" => trans('dashboard/admin.admin.updated')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(ClientRequest $request, $id)
    {
        $client = User::where("user_type", "client")->findOrFail($id);

        $client->update($request->validated() + ["user_type" => "client", 'full_name' => request()->first_name . " " . request()->last_name]);
        //        TODO SEND EMAIL TO USER CONTAIN PASSWORD AND EMAIl
        return (new ClientResource($client->fresh()))->additional(["status" => "success", "message" => trans('dashboard/admin.admin.updated')]);
    }

    public function assignToGroup(Request $request)
    {
        $this->services->assignUsersToGroup($request->ids, $request->group_id);
        return response()->json(["data" => null, "status" => "success", "message" => trans('dashboard/admin.admin.updated')]);
    }

    public function assignToFlow(Request $request)
    {
        //        TODO DELETE USER FROM FLOW AND ACTIVITY NOT COMPLETED
        $this->services->assignUsersToFlow($request->ids, $request->flow_id);
        return response()->json(["data" => null, "status" => "success", "message" => trans('dashboard/admin.admin.updated')]);
    }

    public function unAssignToFlow(Request $request)
    {
        //        TODO DELETE USER FROM FLOW AND ACTIVITY NOT COMPLETED
        $this->services->unAssignToFlow($request->ids, $request->flow_id);
        return response()->json(["data" => null, "status" => "success", "message" => trans('dashboard/admin.admin.updated')]);
    }

    public function clientActivitiesByFlow(User $user, Flow $flow)
    {
        $user_flows = ActivityUser::where(['user_id' => $user->id])->whereIn('activity_id', $flow->activities?->pluck("id"))->get();
        \request()->merge(['user_id' => $user->id, "flow_id" => $flow->id]);


        return ClientActivitiesByFlowResource::collection($user_flows)->additional(['status' => 'success', 'message' => '']);

//        return UserActivities::collection($user_flows)->additional(['status' => "success", "message" => ""]);

    }
}
