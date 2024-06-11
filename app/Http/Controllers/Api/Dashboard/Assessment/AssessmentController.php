<?php

namespace App\Http\Controllers\Api\Dashboard\Assessment;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\QuestionCreator;
use App\Http\Requests\Api\Dashboard\Assessment\AssessmentRequest;
use App\Http\Resources\Api\Dashboard\Activity\ActivityResource;
use App\Models\Activity;
use Exception;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function store(AssessmentRequest $request)
    {
        $at_least_on_correct_value = false;
        foreach ($request->is_correct_answer as $is_correct_answer) {

            if ($is_correct_answer == 1)  {
                $at_least_on_correct_value = true ;
                break;
            }
        }
        if (!$at_least_on_correct_value) {
            return response()->json(['message' => 'select at least one correct answer', 'status' => 'failed'], 422);
        }
        DB::beginTransaction();
        try {
            $activity = Activity::findOrFail($request->activity_id);
            if ($request->assessment_type) (new QuestionCreator($request->assessment_type))->createQuestion()->addQuestion($request, $activity);
            $activity->flow->update([
                'total_score' => $activity->flow->activities()->count()
            ]);
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
}
