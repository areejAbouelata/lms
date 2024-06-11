<?php

namespace App\Http\Resources\Api\Website\Activity;

use App\Http\Resources\Api\Website\Flow\FlowResource;
use App\Models\{ActivityUser, FlowUser, UserEvaluation};
use App\Models\{UserQuestionAnswer};
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $activity_user = ActivityUser::where(['user_id' => auth('api')->id(), "activity_id" => $this->id])->first();

        $flow_user = FlowUser::where(['user_id' => auth('api')->id(), "flow_id" => $this->flow_id])->first();

        $user_evalution = UserEvaluation::where(['user_id' => auth('api')->id(), "flow_id" => $this->flow_id])->get();

        $has_attachment = isset($this->has_attachment) ? ($this->has_attachment) == 1 ? true : false : false;
        return [
            "id" => $this->id,
            "image" => $this->image,
            "flow" => FlowResource::make($this->flow), // resource
            "type" => $this->type,
//            "max_trails" => $this->type == "assessment" ? $this->max_trails : 0,
            "current_trail" => $this->activityUser()->first()->trails + 1,
            "assessment_type" => $this->assessment_type,
            "duration_type" => $this->duration_type,
            "start_date" => $activity_user->start_date,
            "end_date" => $activity_user->end_date,
            "status" => $activity_user->status,
            "due_date" => Carbon::parse($activity_user->end_date)->diffInHours(Carbon::now()),
            "duration" => $this->duration,
            "step_number" => $this->step_number,
            "desc" => $this->desc,
            "attachment" => $this->attachmentAttribute(),
            "user_attachment" => $activity_user?->attachment,
            'questions' => QuestionResource::collection($this->questions),
            'questions_count' => $this->questions?->count(),
            "answered_questions_count" => UserQuestionAnswer::where("activity_id", $this->id)->where('user_id', auth('api')->id())->count(),
            "correct_answers_score" => UserQuestionAnswer::where('user_id', auth('api')->id())->where('activity_id', $this->id)->where('is_correct', 1)->count(),
            "next_step_activity_id" => ActivityUser::where('status', "pending")->whereHas('activity', function ($q) {
                $q->where('flow_id', $this->flow_id);
            })->first()?->activity_id,
            "has_attachment" => $has_attachment,
            "has_note" => (bool)$this->has_note,
            "note_question" => $this->note_question,
            "is_completed" => $activity_user ? $activity_user->is_completed : 0, //$activity_user->is_completed,
            "old_user_score" => $this->user_current_score,
            "user_score" => $activity_user && $this->total_score > 0 ? ($activity_user->score / $this->total_score) * 100 : 0,
            "bench_score" => "80%",
            'rate' => (double)@$flow_user->rate,
            'is_rate' => (double)@$flow_user->rate != null ? true : false,
            "user_note" => $activity_user->note,
            'evalution_form' => $user_evalution ? EvalutionUserFormResource::collection($user_evalution) : [],
//           TODO  get is completed
//           TODO remarks
//           TODO is completed task
        ];
    }
}
