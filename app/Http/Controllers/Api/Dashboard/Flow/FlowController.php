<?php

namespace App\Http\Controllers\Api\Dashboard\Flow;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\QuestionCreator;
use App\Http\Requests\Api\Dashboard\Flow\FlowRequest;
use App\Http\Resources\Api\Dashboard\Client\ClientResource;
use App\Http\Resources\Api\Dashboard\Flow\{FlowResource, MasterFlowResource};
use App\Models\Activity;
use App\Models\AppMedia;
use App\Models\Flow;
use Illuminate\Http\Request;

class FlowController extends Controller
{
    public function index(Request $request)
    {
        $flow = Flow::latest()->when(request()->keyword, function ($query) {
            $query->orWhereTranslationLike('name', '%' . request()->keyword . '%')
                ->orWhereTranslationLike('desc', '%' . request()->keyword . '%');
        })
            ->paginate(25);
        return MasterFlowResource::collection($flow)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function show(Flow $flow)
    {
        return FlowResource::make($flow)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function indexWithoutPagination(Request $request)
    {
        $flow = Flow::latest()->get();
        return FlowResource::collection($flow)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function store(FlowRequest $request)
    {
        $flow = Flow::create($request->validated());
        return FlowResource::make($flow)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function update(FlowRequest $request, Flow $flow)
    {
        $flow->update($request->validated());
        return FlowResource::make($flow)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function destroy(Flow $flow)
    {
        $flow->delete();
        return FlowResource::make($flow)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    //    flow users
    public function users(Request $request, Flow $flow)
    {
        $request->validate(['group_ids' => 'nullable|array', 'department_id' => 'nullable|array']);

        $users =   $flow->users()->when($request->group_ids, function ($q) use ($request) {
            $q->whereIn("group_id", $request->group_ids);
        })
            ->when($request->department_id, function ($q) use ($request) {
                $q->whereIn("department_id", $request->department_id);
            })->paginate(25);

        return ClientResource::collection($users)->additional([
            'status' => 'success',
            'message' => ""
        ]);
    }

    public function copyFlow(Flow $flow)
    {
        $new_flow = Flow::create([
            'en' => [
                'name' => $flow->translate('en')->name . "Copy",
                'desc' => $flow->translate('en')->desc . "Copy",
            ],
            'ar' => [
                'name' => $flow->translate('ar')->name . "Copy",
                'desc' => $flow->translate('ar')->desc . "Copy",
            ],
            'is_active' => $flow->is_active
        ]);
        foreach ($flow->activities as $activity) {
            $data = [
                "flow_id" => $new_flow->id,
                "type" => $activity->type,
                "assessment_type" => $activity->assessment_type,
                "duration_type" => $activity->duration_type,
                "duration" => $activity->duration,
                "is_active" => $activity->is_active,
                "step_number" => $activity->step_number,
                "note_question" => $activity->note_question,
                "has_note" => $activity->has_note
            ];

            $new_activity = Activity::create($data +
                ['en' => [
                    'desc' => $flow->desc . "Copy"
                ],
                    'ar' => ['desc' => $flow->desc . "Copy"
                    ],
                    'step_number' => Activity::where('flow_id', $new_flow->id)->count() + 1,
                ]);
//            attachments
            $medias = AppMedia::where([
                'app_mediaable_type' => 'App\Models\Activity',
                'app_mediaable_id' => $activity->id,
            ])->get();
            foreach ($medias as $media) {
                AppMedia::create([
                    'app_mediaable_type' => 'App\Models\Activity',
                    'app_mediaable_id' => $new_activity->id,
                    'media' => $media->media,
                    'media_type' => $media->media_type,
                    'short_link' => $media->short_link,
                    'option' => $media->option,
                ]);
            }

//            create assessment
            (new QuestionCreator("fill_blank"))->createQuestion()->addQuestionsCopy($activity, $new_activity);
            (new QuestionCreator("choice"))->createQuestion()->addQuestionsCopy($activity, $new_activity);
            (new QuestionCreator("drag_drop"))->createQuestion()->addQuestionsCopy($activity, $new_activity);
        }
        return FlowResource::make($new_flow)->additional(['status' => 'success', 'message' => '']);
    }
}
