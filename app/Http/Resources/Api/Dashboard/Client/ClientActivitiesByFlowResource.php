<?php

namespace App\Http\Resources\Api\Dashboard\Client;

use App\Http\Resources\Api\Dashboard\Client\ActivityResource;
use App\Http\Resources\Api\Dashboard\Flow\FlowResource;
use App\Models\ActivityUser;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ClientActivitiesByFlowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $has_attachment = isset($this->has_attachment) ? ($this->has_attachment) == 1 ? true : false : false;
        $activity_user = ActivityUser::where(['user_id' => $this->client->id, "activity_id" => $this->activity->id])->first();
        return [
            'id' => $this->id,
            "client" => SimpleClientResource::make($this->client),
            'activity' => [
                "id" => $this->activity->id,
                "image" => $this->activity->image,
                "flow" => FlowResource::make($this->activity->flow), // resource
                "type" => $this->activity->type,
                "max_trails" => $this->activity->max_trails,
                "assessment_type" => $this->activity->assessment_type,
                "duration_type" => $this->activity->duration_type,
                "duration" => $this->activity->duration,
                "is_active" => (bool)$this->activity->is_active,
                "step_number" => $this->activity->step_number,
                "answer" => $this->activity->assessment?->answer,
                "desc" => $this->activity->desc,
                "attachment" =>[ $this->attachment],
                "questions" => \App\Http\Resources\Api\Dashboard\Client\QuestionResource::collection($this->activity->questions),
                "has_attachment" => $has_attachment,
            ],
            "is_correct" => $this->is_correct,
            "finished_at" => $this->finished_at ? Carbon::parse($this->finished_at)->format("Y-m-d H:i") : null,
            "start_date" => $this->start_date ? Carbon::parse($this->start_date)->format("Y-m-d H:i") : null,
            "end_date" => $this->end_date ? Carbon::parse($this->end_date)->format("Y-m-d H:i") : null,
            "total_answers" => $this->total_answers,
            "status" => $this->status,
            "note" => $this->note,
            "remarks" => $this->remarks,
            "is_completed" => $activity_user->is_completed,
//            "old_user_score" => $this->user_current_score,
//            "user_score" => $activity_user && $this->total_score > 0 ? ($activity_user->score / $this->total_score) * 100 : 0,
            "user_score" => $activity_user->activity->total_score > 0 ? ($activity_user->score / $activity_user->activity->total_score) * 100 : 0,
            "bench_score" => "80%"
        ];
    }
}
