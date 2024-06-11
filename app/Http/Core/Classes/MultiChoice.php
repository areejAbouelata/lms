<?php

namespace App\Http\Core\Classes;

use App\Http\Core\Interfaces\QuestionInterface;
use App\Models\Activity;
use App\Models\ActivityAnswerUser;
use App\Models\ActivityUser;
use App\Models\FlowUser;
use App\Models\Question;
use App\Models\UserQuestionAnswer;

class MultiChoice implements QuestionInterface
{
//    public $data, $activity;

    public function __construct()
    {
//        $this->data = $data;
//        $this->activity = $activity;
    }

    public function addQuestion($data, $activity)
    {
        $i = 0;
        $question = Question::create([
            'activity_id' => $activity->id,
            'question' => $data->question,
            'assessment_type' => $data->assessment_type,

        ]);
        $activity->update([
            'total_score' => ++$activity->total_score
        ]);
        foreach ($data->answer as $answer) {
            $activity->assessments()->create([
                'answer' => $answer, 'is_correct_answer' => $data->is_correct_answer[$i], 'question_id' => $question->id
            ]);
            $i++;
        }
    }

    public function addQuestionsCopy($old_activity, $new_activity)
    {
        $i = 0;
        $old_activity = Activity::find($old_activity->id);
        $old_questions = Question::where([
            'activity_id' => $old_activity->id, "assessment_type" => "choice"
        ])->get();
        foreach ($old_questions as $old_question) {
            $this->addSingleCopyQuestion($old_question, $old_activity, $new_activity);
        }
    }

    public function addSingleCopyQuestion($old_question, $old_activity, $new_activity)
    {

        $new_question = Question::create([
            'activity_id' => $new_activity->id,
            'question' => $old_question->question,
            'assessment_type' => $old_question->assessment_type,

        ]);
        $new_activity->update([
            'total_score' => ++$new_activity->total_score
        ]);
        $i = 0;
        foreach ($old_activity->assessments()->where('question_id' , $old_question->id)->get() as $answer) {
            $new_activity->assessments()->create([
                'answer' => $answer->answer, 'is_correct_answer' => $answer->is_correct_answer, 'question_id' => $new_question->id
            ]);
            $i++;
        }
    }

    public function answer($request, $activity, $user)
    {
        $i = 0;
        $questions_count = $activity->questions()->count();

        foreach ($request->answer_ids as $answer_id) {
            $is_correct = $activity->assessments()->where(['assessments.id' => $answer_id])->first()->is_correct_answer == $request->answers[$i] ? true : false;
            ActivityAnswerUser::updateOrCreate([
                'activity_id' => $activity->id,
                'user_id' => auth('api')->user()->id,
                'question_id' => $request->question_id,
                'user_assessment_id' => $answer_id,
            ], [
                'activity_id' => $activity->id,
                'user_id' => auth('api')->user()->id,
                'question_id' => $request->question_id,
                'correct_assessment_id' => $activity->assessments()->where("id", $answer_id)->first()->id,
                'user_assessment_id' => $answer_id,
                'is_correct' => $is_correct,
            ]);
            $i++;
        }
        $is_correct_activity = $activity->actiitiyUserAnswers()->where('question_id', $request->question_id)->where('user_id', auth('api')->id())->where('is_correct', 0)->count() ? false : true;
        $user_question_answer = UserQuestionAnswer::where([
            'activity_id' => $activity->id,
            'question_id' => $request->question_id,
            'user_id' => auth('api')->user()->id,
        ])->first();
        $activity_user = ActivityUser::where(['user_id' => auth('api')->user()->id, "activity_id" => $activity->id])->first();
        if ($user_question_answer) {
            $user_question_answer->update([
                "trails" => ++$user_question_answer->trails,
                'is_correct' => $is_correct_activity,
                'is_answered' => 1
            ]);
            $user_answers_for_same_question = UserQuestionAnswer::where(['activity_id' => $activity->id, 'user_id' => auth('api')->user()->id])->pluck('trails');
            // check if all

        } else {
            UserQuestionAnswer::create([
                'activity_id' => $activity->id,
                'question_id' => $request->question_id,
                'user_id' => auth('api')->user()->id,
                "trails" => 1,
                'is_correct' => $is_correct_activity,
                'is_answered' => 1
            ]);
        }
        $correct_answers_count = UserQuestionAnswer::where(['activity_id' => $activity->id,
            'user_id' => auth('api')->user()->id, 'is_correct' => true])->count();

        $answers_count_cycle = UserQuestionAnswer::where([
            'activity_id' => $activity->id,
            'user_id' => auth('api')->user()->id,
            'is_answered' => 1
        ])->count();
        $activity_user->update([
            "score" => $correct_answers_count,
            "total_answers" => $questions_count
        ]);
        $answers_count = UserQuestionAnswer::where(['activity_id' => $activity->id,
            'user_id' => auth('api')->user()->id])->count();
        if ($questions_count == $answers_count_cycle) {
            $activity_user->update([
                "is_completed" => 1
            ]);
        } else {
            $activity_user->update([
                "is_completed" => 0
            ]);
        }
        FlowUser::where([
            "flow_id" => $activity->flow->id,
            "user_id" => auth('api')->id()])
            ->update([
                'assessment_score' => ActivityUser::where(['user_id' => auth('api')->user()->id])->where(["status" => "finished"])->whereIn("activity_id", $activity->flow->activities()->where("type", "assessment")->pluck('id'))->count()
                , 'score' => ActivityUser::whereIn('activity_id', $activity->flow->activities()->pluck('id'))->where("status", "finished")->count(),
                'total_score' => $activity->flow->activities()->count(),
            ]);
    }

    public function setScore()
    {

    }
}
