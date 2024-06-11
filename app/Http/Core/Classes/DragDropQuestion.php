<?php

namespace App\Http\Core\Classes;

use App\Http\Core\Interfaces\QuestionInterface;
use App\Models\ActivityAnswerUser;
use App\Models\ActivityUser;
use App\Models\FlowUser;
use App\Models\Question;
use App\Models\UserQuestionAnswer;

class DragDropQuestion implements QuestionInterface
{

//    public $data, $activity;

    public function __construct()
    {
//        $this->data = $data;
//        $this->activity = $activity;
    }

    public function addQuestion($data, $activity)
    {
        // TODO: Implement addQuestion() method.
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
            $assessment = $activity->assessments()->create([
                'answer' => $answer, 'is_correct_answer' => $data->is_correct_answer[$i], 'question_id' => $question->id
            ]);
            $activity->assessments()->create([
                'answer' => $data->match_answer[$i], 'is_correct_answer' => $data->is_correct_answer[$i], "match_answer_id" => $assessment->id, 'question_id' => $question->id
            ]);
            $i++;
        }
    }

    public function addQuestionsCopy($old_activity, $new_activity)
    {

        $old_questions = Question::where([
            'activity_id' => $old_activity->id,
            'assessment_type' => "drag_drop",
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

        foreach ($old_activity->assessments()->where('question_id', $old_question->id)->get() as $answer) {
            $assessment = $new_activity->assessments()->create([
                'answer' => $answer->answer, 'is_correct_answer' => $answer->is_correct_answer, 'question_id' => $new_question->id
            ]);
            $old_match_assessment = $old_activity->assessments()->where('match_answer_id', $answer->id)->first();
            if ($old_match_assessment) {
                $new_activity->assessments()->create([
                    'answer' => $old_match_assessment->answer, 'is_correct_answer' => $old_match_assessment->is_correct_answer, "match_answer_id" => $assessment->id, 'question_id' => $new_question->id
                ]);
            }
            $i++;

        }
    }

    public function answer($request, $activity, $user)
    {
        $i = 0;
        foreach ($request->answer_ids as $answer_id) {
            $is_correct_answer = $activity->assessments()->where('question_id', $request->question_id)->where("assessments.match_answer_id", $answer_id)->first()->id == (int)$request->match_answer_ids[$i] ? true : false;
            ActivityAnswerUser::updateOrCreate([
                "activity_id" => $activity->id,
                "user_id" => auth('api')->id(),
                "correct_assessment_id" => $answer_id,
                'question_id' => $request->question_id,
            ], [
                "activity_id" => $activity->id,
                "correct_assessment_id" => $answer_id,
                "correct_match_id" => $activity->assessments()->where("assessments.match_answer_id", $answer_id)->first()->id,
                "user_assessment_id" => $answer_id,
                "user_match_id" => $request->match_answer_ids[$i],
                "user_id" => auth('api')->id(),
                'question_id' => $request->question_id,
                "is_correct" => $is_correct_answer,
            ]);
            $i++;
        }
        $activity_answers_correction = ActivityAnswerUser::where(['activity_id' => $activity->id, "is_correct" => 0, 'question_id' => $request->question_id, "user_id" => auth('api')->id(),])->count() ? false : true;
        $is_correct_activity = $activity->actiitiyUserAnswers()->where('question_id', $request->question_id)->where('user_id', auth('api')->id())->where('is_correct', 0)->count() ? false : true;
//create question
        $user_question_answer = UserQuestionAnswer::where([
            'activity_id' => $activity->id,
            'question_id' => $request->question_id,
            'user_id' => auth('api')->user()->id,
        ])->first();
        $activity_user = ActivityUser::where([
            'user_id' => auth('api')->user()->id,
            "activity_id" => $activity->id])->first();
        $questions_count = $activity->questions()->count();
        if ($user_question_answer) {
            $user_question_answer->update([
                "trails" => ++$user_question_answer->trails,
//                'is_correct' => $activity_answers_correction,
                'is_correct' => $is_correct_activity,
                'is_answered' => 1
            ]);
            $user_answers_for_same_question = UserQuestionAnswer::where(['activity_id' => $activity->id, 'user_id' => auth('api')->user()->id])->pluck('trails');
        } else {
            UserQuestionAnswer::create(['activity_id' => $activity->id,
                'question_id' => $request->question_id,
                'user_id' => auth('api')->user()->id,
                "trails" => 1,
//                'is_correct' => $activity_answers_correction,
                'is_correct' => $is_correct_activity,
                'is_answered' => 1
            ]);
        }
        $correct_answers_count = UserQuestionAnswer::where(['activity_id' => $activity->id,
            'user_id' => auth('api')->user()->id, 'is_correct' => true])->count();
        $answers_count = UserQuestionAnswer::where(['activity_id' => $activity->id,
            'user_id' => auth('api')->user()->id])->count();

        $answers_count_cycle = UserQuestionAnswer::where([
            'activity_id' => $activity->id,
            'user_id' => auth('api')->user()->id,
            'is_answered' => 1
        ])->count();
        $activity_user->update([
            "score" => $correct_answers_count,
            "total_answers" => $answers_count,
        ]);
        if ($questions_count == $answers_count_cycle) {
            $activity_user->update([
                "is_completed" => 1
            ]);
        } else {
            $activity_user->update([
                "is_completed" => 0
            ]);
        }
        FlowUser::where(["flow_id" => $activity->flow->id,
            "user_id" => auth('api')->id()])->update([
            'assessment_score' => ActivityUser::where(['user_id' => auth('api')->user()->id])->where(["status" => "finished"])->whereIn("activity_id", $activity->flow->activities()->where("type", "assessment")->pluck('id'))->count(),
            'score' => ActivityUser::whereIn('activity_id', $activity->flow->activities()->pluck('id'))->where("status", "finished")->count(),
            'total_score' => $activity->flow->activities()->count(),
        ]);
    }
}
