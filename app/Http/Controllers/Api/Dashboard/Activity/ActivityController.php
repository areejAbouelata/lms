<?php

namespace App\Http\Controllers\Api\Dashboard\Activity;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\QuestionCreator;
use App\Http\Requests\Api\Dashboard\Activity\ActivityRequest;
use App\Http\Resources\Api\Dashboard\Activity\ActivityResource;
use App\Http\Services\ClientServices;
use App\Models\Activity;
use App\Models\ActivityQuestionUser;
use App\Models\ActivityUser;
use App\Models\Assessment;
use App\Models\Flow;
use App\Models\Question;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public $clientServices;

    public function __construct(ClientServices $clientServices)
    {
        $this->clientServices = $clientServices;
    }

    public function index(Request $request)
    {
        $activities = Activity::
        when(request()->keyword, function ($query) {
            $query->whereTranslationLike('desc', '%' . request()->keyword . '%');
        })->
        latest()->paginate(25);
        return ActivityResource::collection($activities)->additional(
            [
                'status' => "success",
                "message" => ""
            ]
        );
    }

    public function flowActivities(Flow $flow, Request $request)
    {
        $activities = $flow->activities()->when(request()->keyword, function ($query) {
            $query->whereTranslationLike('desc', '%' . request()->keyword . '%');
        })->latest()->paginate(25);
        return ActivityResource::collection($activities)->additional(
            [
                'status' => "success",
                "message" => ""
            ]
        );
    }

    public function show(Activity $activity)
    {
        return ActivityResource::make($activity)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function indexWithoutPagination(Request $request)
    {
        $activity = Activity::latest()->get();
        return ActivityResource::collection($activity)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function store(ActivityRequest $request)
    {

        DB::beginTransaction();
        try {
            $data = ["flow_id", "type", "assessment_type", "duration_type", "duration", "is_active", "step_number", "note_question", "has_note",'time_minutes'];
            if ($request->has('has_attachment')) {
                $data[] = "has_attachment";
            }

            $note = $request->type == 'task' ? 1 : 0;
            $activity = Activity::create(array_only($request->validated(), $data) +
                ['en' => ['desc' => $request->en['desc']], 'ar' => ['desc' => $request->ar['desc']],
                    'step_number' => Activity::where('flow_id', $request->flow_id)->count() + 1,

                ]);
            if ($activity->flow->users()->count()) {
                return response()->json([
                    'data' => "",
                    'status' => "fail",
                    "message" => 'Can not Add Activity To Started Flow'
                ], 422);
            }
            if ($request->assessment_type) (new QuestionCreator($request->assessment_type))->createQuestion()->addQuestion($request, $activity);
            $activity->flow->update([
                'total_score' => $activity->flow->activities()->count()
            ]);
            $activityFlowUsers = $activity->flow->users;
            $end = $this->clientServices->activityDuration($activity);
            foreach ($activityFlowUsers as $user) {
                ActivityUser::updateOrCreate([
                    'user_id' => $user->id,
                    "activity_id" => $activity->id,

                ], [
                    'user_id' => $user->id,
                    "activity_id" => $activity->id,
                    "start_date" => Carbon::now(),
                    "end_date" => $end,
                    'status' => 'pending'
                ]);
            }
            DB::commit();
            return ActivityResource::make($activity)->additional([
                'status' => "success",
                "message" => ""
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            dd($exception);
        }
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        return ActivityResource::make($activity)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

//    activate
    public function toggleActive(Activity $activity)
    {
        $activity->update([
            'is_active' => !((boolean)$activity->is_active)
        ]);
        return ActivityResource::make($activity->fresh())->additional([
            "status" => "success", "message" => ""
        ]);
    }

    public function updateQuestion(Question $question, Request $request)
    {
        if ($question->activity->hasUsers()) {
            return response()->json([
                'data' => "",
                'status' => "fail",
                "message" => trans('dashboard.messages.Can not update this question')
            ], 422);
        }
        $request->validate([
            'question' => 'required|string'
        ],
            [
                'question.required' => trans('dashboard.messages.Question is required'),
                'question.string' => trans('dashboard.messages.Question must be string')
            ]);
        $question->update(['question' => $request->question]);
        return response()->json([
            [
                'data' => "",
                'status' => "success",
                "message" => trans('dashboard.messages.success_update')
            ]
        ]);

    }

    public function addAnswer(Question $question, Request $request)
    {
        if ($question->activity->hasUsers()) {
            return response()->json([
                'data' => "",
                'status' => "fail",
                "message" => trans('dashboard.messages.Can not update this question')
            ], 422);
        }
        $assessment_type = $question->assessment_type;
        $request->validate([
            'answer' => 'required|string',
            'is_correct_answer' => 'required|in:0,1',
            'match_answer_id' => "nullable|exists:assessments,id",
        ], [
            'answer.required' => trans('dashboard.messages.Answer is required'),
            'is_correct_answer.required' => trans('dashboard.messages.You should specify this is the correct answer or not'),
            'is_correct_answer.in' => trans('dashboard.messages.Invalid data')
        ]);
        $activity = $question->activity;
        switch ($assessment_type) {
            case 'fill_blank':
                $activity->assessments()->create([
                    'answer' => strtolower($request->answer), 'is_correct_answer' => $request->is_correct_answer, 'question_id' => $question->id
                ]);
                break;

            case 'choice':
                $activity->assessments()->create([
                    'answer' => $request->answer, 'is_correct_answer' => $request->is_correct_answer, 'question_id' => $question->id
                ]);
                break;
            case 'drag_drop':
                $assessment = $activity->assessments()->create([
                    'answer' => $request->answer, 'is_correct_answer' => $request->is_correct_answer, 'question_id' => $question->id
                ]);
                $activity->assessments()->create([
                    'answer' => $request->match_answer, 'is_correct_answer' => $request->is_correct_answer, "match_answer_id" => $assessment->id, 'question_id' => $question->id
                ]);
                break;
        }
//        $activity->assessments()->create([
//            'answer' => strtolower($request->answer), 'is_correct_answer' => $request->is_correct_answer, 'question_id' => $question->id
//        ]);
        return response()->json([
            [
                'data' => "",
                'status' => "success",
                "message" => trans('dashboard.messages.success_add')
            ]
        ]);
    }

//    public function addDragDropAnswer(Question $question, Request $request)
//    {
//        $request->validate([
//            'answer' => 'required|string',
//            'is_correct_answer' => 'required|in:0,1',
//            'match_answer_id' => "nullable|exists:assessments,id",
//        ]);
//        $activity = $question->activity;
//        $assessment = $activity->assessments()->create([
//            'answer' => $request->answer, 'is_correct_answer' => $request->is_correct_answer, 'question_id' => $question->id
//        ]);
//        $activity->assessments()->create([
//            'answer' => $request->match_answer, 'is_correct_answer' => $request->is_correct_answer, "match_answer_id" => $assessment->id, 'question_id' => $question->id
//        ]);
//
//        return response()->json([
//            [
//                'data' => "",
//                'status' => "success",
//                "message" => trans('dashboard.messages.success_add')
//            ]
//        ]);
//    }
//
//    public function addMultiChoiceAnswer(Question $question, Request $request)
//    {
//        $request->validate([
//            'answer' => 'required|string',
//            'is_correct_answer' => 'required|in:0,1'
//        ]);
//        $activity = $question->activity;
//        $activity->assessments()->create([
//            'answer' => $request->answer, 'is_correct_answer' => $request->is_correct_answer, 'question_id' => $question->id
//        ]);
//
//        return response()->json([
//            [
//                'data' => "",
//                'status' => "success",
//                "message" => trans('dashboard.messages.success_add')
//            ]
//        ]);
//    }
//
    public function deleteAnswer(Assessment $assessment)
    {
        if ($assessment->question->activity->hasUsers()) {
            return response()->json([
                'data' => "",
                'status' => "fail",
                "message" => trans('dashboard.messages.Can not delete this assessment')
            ], 422);
        }
        $assessment->delete();
        return response()->json([
            [
                'data' => '',
                'status' => 'success',
                "message" => trans('dashboard.messages.success_delete')
            ]
        ]);
    }
}
