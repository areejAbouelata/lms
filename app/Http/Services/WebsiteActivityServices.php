<?php

namespace App\Http\Services;

use App\Mail\ProcessCompletedToAdmin;
use App\Mail\ProcessCompletedToClient;
use App\Models\ActivityAnswerUser;
use App\Models\ActivityUser;
use App\Models\Question;
use App\Models\User;
use App\Models\UserQuestionAnswer;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Mail;

class WebsiteActivityServices
{
    public function updateStatus($flow)
    {
        $flow->update([
            'status' => $this->checkIfFlowFinished($flow)
        ]);
//        heeeeeeeeeeeeeeeeeeeeeeeeeeeer
        if ($flow->fresh()->status == "finished") {
            $data = [
                'name' => auth('api')->user()->full_name,
                'email' => auth('api')->user()->email,
                'hr_mail' => setting('hr_mail') ?? "'areejibrahim222@gmail.com'",
                "department" => auth('api')->user()->department?->name,
                "position" => auth('api')->user()->job_title,
            ];
            Mail::to(auth('api')->user()->email)->send(new ProcessCompletedToClient($data));
            foreach (User::whereIn('user_type', ['admin', 'super_admin'])->get() as $user) {
                $admin_data = [
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'hr_mail' => setting('hr_mail') ?? "'areejibrahim222@gmail.com'",
                    "position" => auth('api')->user()->job_title,
                    "user_name" => auth('api')->user()->full_name,
                ];
                Mail::to($user->email)->send(new ProcessCompletedToAdmin($admin_data));

            }
        }

    }

    public function checkIfFlowFinished($flow)
    {
        $activities = $flow->activities()->pluck("id");
        $status = ActivityUser::whereIn("activity_id", $activities)->where('status', "pending")->exists() ? "pending" : "finished";
        return $status;
    }

    public function getFlowStatistics($flow)
    {


        $all_activities_ids = $flow->activities()->whereNotIn('type', ['assessment'])->pluck('id');

        // info($all_activities_ids) ;

//        $all_activities_count = $flow->activities()->count();

        $all_activities_count = $flow->activities()->whereNotIn('type', ['assessment'])->count();

        $completed_activities_count = ActivityUser::completedActivities(auth('api')->user(), $all_activities_ids)->count();
        
        info($all_activities_ids) ;
        info("Sds") ;
        $all_assessments_ids = $flow->activities()->where('type', 'assessment')->pluck('id');
//        $all_assessments_ids = $flow->activities()->whereNotIn('type', ['assessment'])->pluck('id');

        $quizzes = Question::whereIn('activity_id', $all_assessments_ids)->count();

        $user_active_flow = auth()->user()->flows()->latest()->first();



        $user_active_flow_activity = $user_active_flow?->activities()->where('type' , 'assessment')->first();


//        $quizzes_success_count = UserQuestionAnswer::where('user_id', auth('api')->id())->where('activity_id', $user_active_flow_activity?->id)->count();

        $quizzes_success_count = UserQuestionAnswer::where('user_id', auth('api')->id())->where('activity_id', $user_active_flow_activity?->id)->count();
            info($user_active_flow_activity) ;
            
        // info($user_active_flow_activity);
//        $quizzes_success_count = UserQuestionAnswer::where('user_id', auth('api')->id())->whereIn('activity_id', $all_assessments_ids)->count();

        $all_tasks_ids = $flow->activities()->where(['type' => 'task', 'is_active' => 1])->pluck('id');
//        $tasks = $flow->activities()->where('type', 'task')->count();
        $tasks = $flow->activities()->where(['type' => 'task', 'is_active' => 1])->count();
        $success_tasks = ActivityUser::successTasks(auth('api')->user(), $all_tasks_ids)->count();

        $start_of_week = Carbon::now()->startOfWeek(CarbonInterface::SATURDAY);
        $end_of_week = Carbon::now()->endOfWeek(CarbonInterface::FRIDAY);

        $completed_this_week_count = ActivityUser::assessmentsByDuration($start_of_week, $end_of_week, auth('api')->user())->count();
        $data = [];
        $data['quizzes_success_rate'] = $quizzes ? round(($quizzes_success_count / $quizzes) * 100) : 0;
        $data['tasks_completion'] = $tasks ? round($success_tasks / $tasks * 100) : 0;
        $data['completion'] = $all_activities_count ? round($completed_activities_count / $all_activities_count * 100) : 0;
        $data['completed_this_week_count'] = $completed_this_week_count;
        return $data;
    }
}
