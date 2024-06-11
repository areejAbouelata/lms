<?php

namespace App\Http\Services;

use App\Jobs\DueTaskToClient;
use App\Mail\NewClient;
use App\Mail\NewClientToAdmin;
use App\Models\Activity;
use App\Models\ActivityUser;
use App\Models\Department;
use App\Models\Flow;
use App\Models\FlowUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ClientServices
{
    public function getClients()
    {
        return User::where("user_type", "client")
            ->when(request()->keyword, function ($query) {
                $query->where(function ($query) {
                    $query->where('full_name', 'LIKE', '%' . request()->keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . request()->keyword . '%');
                });
            })->
            when(request()->country_id, function ($q) {
                $q->whereIn("country_id", request()->country_id);
            })
            ->when(request()->group_ids, function ($q) {
                $q->whereIn("group_id", request()->group_ids);
            })
            ->when(request()->job_title_id, function ($q) {
                $q->whereIn("job_title_id", request()->job_title_id);
            })
            ->when(request()->department_id, function ($q) {
                $q->whereIn("department_id", request()->department_id);
            })->when(request()->flow_id, function ($q) {
                $q->whereHas("flowsUser", function ($q) {
                    $q->whereIn('flow_id', request()->flow_id);
                });
            })
//            TODO SeaRCH BY FLOW
            ->latest()->when(is_numeric(request()->paginate), function ($query) {
                return $query->paginate(request()->paginate);
            }, function ($query) {
                return $query->get();
            });
    }

    public function assignUsersToGroup($user_list, $group_id)
    {
        User::whereIn("id", $user_list)->update(["group_id" => $group_id]);
        return;
    }

    public function assignUsersToFlow($user_list, $flow_id)
    {
        $old_active_flow = Flow::query()->activeFlow()->pluck("id");
        $old_activities = Activity::whereIn("flow_id", $old_active_flow)->pluck("id");
        foreach ($user_list as $id) {

            FlowUser::where(["user_id" => $id])->whereIn("flow_id", $old_active_flow)->where("status", "pending")->update(['status' => "inactive"]);
            ActivityUser::where(["user_id" => $id])->whereIn("activity_id", $old_activities)->where("status", "pending")->update(['status' => "inactive"]);

            FlowUser::updateOrCreate([
                'flow_id' => $flow_id,
                "user_id" => $id
            ], [
                'flow_id' => $flow_id,
                "user_id" => $id,
                'status' => 'pending' ,
                'assigned_at' => Carbon::now()
            ]);

            foreach (Flow::find($flow_id)?->activities as $activity) {
                $end = $this->activityDuration($activity);
                ActivityUser::updateOrCreate([
                    'user_id' => $id,
                    "activity_id" => $activity->id,

                ], [
                    'user_id' => $id,
                    "activity_id" => $activity->id,
                    "start_date" => Carbon::now(),
                    "end_date" => $end,
                    'status' => 'pending'
                ]);

                //                send mail
                $data = [
                    'name' => User::find($id)->full_name,
                    'user_id' => User::find($id)->id,
                    'activity_id' => $activity->id,
                    'email' => User::find($id)->email,
                    'url' => "https://onboarding.gulf-banquemisr.ae/user/assignments?status=pending",
                    'hr_mail' => setting('hr_mail') ?? "'areejibrahim222@gmail.com'",
                    "department" => User::find($id)->department?->name,
                    "position" => User::find($id)->job_title,
                    "user_name" => User::find($id)?->full_name,
                    "task_title" => $activity->desc,
                ];
                $admin_data = [
                    'name' => User::find($id)->full_name,
                    'user_id' => User::find($id)->id,
                    'activity_id' => $activity->id,
                    'email' => User::find($id)->email,
                    'url' => "https://onboarding.gulf-banquemisr.ae/admin/flows",
                    'hr_mail' => setting('hr_mail') ?? "'areejibrahim222@gmail.com'",
                    "user_name" => User::find($id)?->full_name,
                    "task_title" => $activity->desc,
                ];
                DueTaskToClient::dispatch($data, $admin_data)
                    ->delay(Carbon::parse($end)->addDays(3));
            }

        }
        return;
    }

    public function activityDuration($activity)
    {
        $end = null;
        switch ($activity->duration_type) {
            case 'hour':
                $end = Carbon::now()->addHours($activity->duration);
                break;
            case 'day':
                $end = Carbon::now()->addDays($activity->duration);
                break;
            case 'month':
                $end = Carbon::now()->addMonths($activity->duration);
                break;
        }
        return $end;
    }

    public function unAssignToFlow($user_list, $flow_id)
    {
        foreach ($user_list as $id) {
            FlowUser::where([
                'flow_id' => $flow_id,
                "user_id" => $id
            ])->delete();
            foreach (Flow::find($flow_id)?->activities as $activity) {
                ActivityUser::where([
                    'user_id' => $id,
                    "activity_id" => $activity->id
                ])->delete();
            }

        }
        return;
    }

    public function sendMail($request)
    {
        $data = [
            'password' => $request->password,
            'name' => $request->full_name,
            'email' => $request->email,
            'url' => "https://onboarding.gulf-banquemisr.ae/auth/user/login",
            "position" => $request->job_title,
            'hr_mail' => setting('hr_mail') ?? "'areejibrahim222@gmail.com'"
        ];
        Mail::to($request->email)->send(new NewClient($data));
        $this->sendMailToAdmins($request);
    }

    public function sendMailToAdmins($request)
    {
        $admins = User::whereIn('user_type', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            $data = [
                'client_name' => $request->full_name,
                'email' => $request->full_name,
                'hr_mail' => setting('hr_mail') ?? "'areejibrahim222@gmail.com'",
                "role" => $request->job_title_id,
                "department" => Department::find($request->department_id)->name,
                "admin_name" => $admin->full_name,
            ];
            Mail::to($admin->email)->send(new NewClientToAdmin($data));
        }
    }

    public function sendMailUsingExcel($request)
    {
        $data = [
            'password' => $request['password'],
            'name' => $request['full_name'],
            'email' => $request['email'],
            'url' => "https://onboarding.gulf-banquemisr.ae/auth/user/login",
            "position" => $request['job_title'],
            'hr_mail' => setting('hr_mail') ?? "'areejibrahim222@gmail.com'"
        ];
        Mail::to($request['email'])->send(new NewClient($data));
        $this->sendMailToAdminsUsingExcel($request);
    }

    public function sendMailToAdminsUsingExcel($request)
    {
        $admins = User::whereIn('user_type', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            $data = [
                'client_name' => $request['full_name'],
                'email' => $request['full_name'],
                'hr_mail' => setting('hr_mail') ?? "'areejibrahim222@gmail.com'",
                "role" => " ",
                "department" => Department::find($request['department_id'])?->name ?? " ",
                "admin_name" => $admin->full_name,
            ];
            Mail::to($admin->email)->send(new NewClientToAdmin($data));
        }
    }
}
