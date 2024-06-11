<?php

namespace App\Http\Controllers\Api\Website\Activity;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\QuestionCreator;
use App\Http\Requests\Api\Website\Activity\ActivityRemqrkRequest;
use App\Http\Requests\Api\Website\Assessment\AnswerMultiChoiceRequest;
use App\Http\Requests\Api\Website\Assessment\DragDropQuestionRequest;
use App\Http\Requests\Api\Website\Assessment\FillBlankAnswerRequest;
use App\Http\Resources\Api\Website\Activity\ActivityResource;
use App\Http\Services\WebsiteActivityServices;
use App\Models\Activity;
use App\Models\ActivityUser;
use App\Models\Flow;
use App\Models\FlowUser;
use App\Models\UserQuestionAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public $activityServices;

    public function __construct(WebsiteActivityServices $services)
    {
        $this->activityServices = $services;
    }

    public function markAsCompleted($activity)
    {
        $activity = auth('api')->user()->activities()->findOrFail($activity);
        $user_activity = ActivityUser::where(['user_id' => auth('api')->id(), 'activity_id' => $activity->id])->first();
        $user_current_score = $activity->total_score > 0 ? ($user_activity->score / $activity->total_score) * 100 : 0;
        $activity->user_current_score = $user_current_score;
        // check if activity is assessment to handle score
        if ($activity->type == "assessment") {
            $trails = ++$user_activity->trails;
            if ($activity->total_score > 0) {
                $user_current_score = ($user_activity->score / $activity->total_score) * 100;
            } else {
                $user_current_score = 0;
            }
            // if user failed in activity exam
            if ($user_current_score < 80) {
                $user_activity->update([
                    'trails' => ++$trails,
                    'is_completed' => 0,
                    'score' => 0
                ]);
                $answers = UserQuestionAnswer::where([
                    'user_id' => auth('api')->id(),
                    'activity_id' => $activity->id,
                ])->get();
                foreach ($answers as $answer) {
                    $answer->update([
                        'is_answered' => 0
                    ]);
                }
            } else {
                $user_activity->update([
                    'trails' => $trails,
                    'status' => "finished",
                    "finished_at" => Carbon::now(),
                ]);
            }
        } else {
            $user_activity->update([
                'status' => "finished",
                "finished_at" => Carbon::now(),
            ]);
        }
        $activities_ids = $activity->flow->activities()->pluck('id');
        // dd(ActivityUser::whereIn('activity_id', $activities_ids)->where("user_id" , auth('api')->id())->where("status", "finished")->count()) ;
        FlowUser::where([
            'user_id' => auth('api')->id(),
            "flow_id" => $activity->flow->id
        ])->update([
            'score' => ActivityUser::whereIn('activity_id', $activities_ids)->where("user_id" , auth('api')->id())->where("status", "finished")->count(),
            'total_score' => $activity->flow->activities()->count(),
//            TODO total == score then finished
            "status" => ActivityUser::whereIn('activity_id', $activities_ids)->where("status", "finished")->where("user_id" , auth('api')->id())->count() == $activity->flow->activities()->count() ? "finished" : "pending"
        ]);
        $this->activityServices->updateStatus($activity->flow);
        return ActivityResource::make($activity)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function activities()
    {
        $flow = auth('api')->user()->flows()->where('is_active', 1)->
        whereHas('flowUsers', function ($q) {
            $q
//                ->where('status', "<>", "inactive")
//                ->where('status', "<>", "finished")
                ->where('user_id', auth('api')->id());
        })->latest()
            ->firstOrFail();
        $activities = auth('api')->user()->activities()
            ->where(['flow_id' => $flow->id, 'is_active' => 1])
            ->when(request()->status, function ($q) {
                $q->whereHas('activityUser', function ($q) {
                    $q->where('status', \request()->status)->where('user_id', auth('api')->id());
                });
            })
            ->distinct()->get()->sortBy('duration_length');
        foreach ($activities as $activity) {
            $user_activity = ActivityUser::where(['user_id' => auth('api')->id(), 'activity_id' => $activity->id])->first();
            $user_current_score = $activity->total_score > 0 ? ($user_activity->score / $activity->total_score) * 100 : 0;
            $activity->user_current_score = $user_current_score;
        }
        return ActivityResource::collection($activities)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function activity($activity)
    {
        $activity = Activity::whereHas('activityUser', function ($q) {
            $q->where('user_id', auth('api')->id());
        })->where('id', $activity)->firstOrFail();
        return ActivityResource::make($activity)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function activityRemark(ActivityRemqrkRequest $request, $activity)
    {
        $validated_data = [];
        $validated_data['remarks'] = $request->remarks;
        if ($request->has('note')) {
            $validated_data['note'] = $request->note;
            $validated_data['is_completed'] = 1;
        }
        $activity = auth('api')->user()->activities()->where('type', "<>", "assessment")->findOrFail($activity);
//        $activity->update();
        $user_activity = ActivityUser::where(['user_id' => auth('api')->id(), 'activity_id' => $activity->id])->first();
        $user_activity->update($validated_data);

//        $user_activity->update([
//            'remarks' => $request->remarks
//        ]);
        return ActivityResource::make($activity->fresh())->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function answerMultiChoiceAssessment(AnswerMultiChoiceRequest $request, $activity)
    {
        $activity = auth('api')->user()->activities()->where(["activities.id" => $activity, 'type' => "assessment"])->firstOrFail();
        $user_activity = ActivityUser::where(['user_id' => auth('api')->id(), 'activity_id' => $activity->id])->first();
//        $user_current_score = $user_activity->score;

        $user_current_score = ($user_activity->score / $activity->total_score) * 100;
        $activity->user_current_score = $user_current_score;

        (new QuestionCreator('choice'))->createQuestion()->answer($request, $activity, auth('api')->user());

        $this->activityServices->updateStatus($activity->flow);
        // dd($activity);

        return ActivityResource::make($activity)->additional([
            'status' => "success",
            "message" => ""
        ]);
//        return ActivityResource::make($activity->fresh())->additional([
//            'status' => "success",
//            "message" => ""
//        ]);
    }

    public function fillBlankAnswer(FillBlankAnswerRequest $request, $activity)
    {

        $fill_blank_activity = auth('api')->user()->activities()->where('activities.id', (int)$activity)->where(['type' => "assessment"])->firstOrFail();
        $user_activity = ActivityUser::where(['user_id' => auth('api')->id(), 'activity_id' => $fill_blank_activity->id])->first();
        $user_current_score = $user_activity->score;

        $fill_blank_activity->user_current_score = $user_current_score;
        (new QuestionCreator('fill_blank'))->createQuestion()->answer($request, $fill_blank_activity, auth('api')->user());

        $this->activityServices->updateStatus($fill_blank_activity->flow);

        return ActivityResource::make($fill_blank_activity)->additional([
            'status' => "success",
            "message" => ""
        ]);
//        return ActivityResource::make($fill_blank_activity->fresh())->additional([
//            'status' => "success",
//            "message" => ""
//        ]);
    }

    public function dragDropQuestion(DragDropQuestionRequest $request, $activity)
    {
        $activity = auth('api')->user()->activities()->where(["activities.id" => $activity, 'type' => "assessment"])->firstOrFail();
        $user_activity = ActivityUser::where(['user_id' => auth('api')->id(), 'activity_id' => $activity->id])->first();
        $user_current_score = ($user_activity->score / $activity->total_score) * 100;
        $activity->user_current_score = $user_current_score;
        (new QuestionCreator('drag_drop'))->createQuestion()->answer($request, $activity, auth('api')->user());

        $this->activityServices->updateStatus($activity->flow);

        return ActivityResource::make($activity)->additional([
            'status' => "success",
            "message" => ""
        ]);
//        return ActivityResource::make($activity->fresh())->additional([
//            'status' => "success",
//            "message" => ""
//        ]);
    }

    public function statistics(Request $request)
    {
        $no_flow_response = [
            "quizzes_success_rate" => 0,
            "tasks_completion" => 0,
            "completion" => 0,
            "completed_this_week_count" => 0
        ];
        $active_flow = FlowUser::where('user_id', auth('api')->id())->latest()->first();
        $flow = Flow::find($active_flow?->flow_id);
//        $response = $flow ? $this->activityServices->getFlowStatistics($flow) : [];
        $response = $flow ? $this->activityServices->getFlowStatistics($flow) : $no_flow_response;
        return response()->json(['data' => $response, 'status' => 'success', 'message' => '']);
    }

    public function showCertificate(Flow $flow)
    {
        // dump(FlowUser::find($flow->id )) ;
        return response()->json([
            'data' => [
                'user' => auth('api')->user()->first_name . ' ' . auth('api')->user()->last_name,
                'flow' => $flow->name,
                'flow_status' => FlowUser::firstWhere(['flow_id' => $flow->id, "user_id" => auth('api')->id()])->status
            ],
            'status' => 'success',
            'message' => null
        ]);
    }

    public function showAllCertificates()
    {
        $user = auth('api')->user();
        $user_flows = $user->flows;
        $flows = [];
        foreach ($user_flows as $user_flow) {
            if (FlowUser::firstWhere(["user_id" => auth('api')->id(), 'flow_id' => $user_flow->id])->status == "finished") {
                $flows[] = ['name' => $user_flow->name, 'flow_status' =>FlowUser::firstWhere(["user_id" => auth('api')->id(), 'flow_id' => $user_flow->id])->status ];
            }
        }
        return response()->json([
            'data' => [
                'user' => auth('api')->user()->full_name,
                'flows' => $flows
            ],
            'status' => 'success',
            'message' => null
        ]);
    }
}
