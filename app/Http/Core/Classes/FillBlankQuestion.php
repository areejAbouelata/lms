<?php

namespace App\Http\Core\Classes;

use App\Http\Core\Interfaces\QuestionInterface;
use App\Models\ActivityAnswerUser;
use App\Models\ActivityUser;
use App\Models\FlowUser;
use App\Models\Question;
use App\Models\UserQuestionAnswer;

class FillBlankQuestion implements QuestionInterface
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
            $activity->assessments()->create([
                'answer' => strtolower($answer), 'is_correct_answer' => $data->is_correct_answer[$i], 'question_id' => $question->id
            ]);

            $i++;
        }
    }

    public function addQuestionsCopy($old_activity, $new_activity)
    {
        // TODO: Implement addQuestion() method.

        $old_questions = Question::where([
            'activity_id' => $old_activity->id,
            'assessment_type' => "fill_blank",

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
            'total_score' => ++$new_question->total_score
        ]);
        $i = 0;
        foreach ($old_activity->assessments()->where('question_id' , $old_question->id)->get() as $answer) {
            $new_activity->assessments()->create([
                'answer' => strtolower($answer->answer), 'is_correct_answer' => $answer->is_correct_answer, 'question_id' => $new_question->id
            ]);

            $i++;
        }
    }

    public function answer($request, $fill_blank_activity, $user)
    {
        $correct_assessment = $fill_blank_activity->assesments()->where('question_id' , $request->question_id)->first();

        // info($correct_assessment->answer);
        
        $is_correct = strtolower($correct_assessment->answer) == strtolower($request->answer) ? true : false;
        $questions_count = $fill_blank_activity->questions()->count();

        ActivityAnswerUser::updateOrCreate([
            'activity_id' => $fill_blank_activity->id,
            'user_id' => auth('api')->user()->id,
            'question_id' => $request->question_id,
        ], [
            'activity_id' => $fill_blank_activity->id,
            'correct_assessment_id' => $fill_blank_activity->assessments()->where("is_correct_answer", 1)->first()->id,
            'user_id' => auth('api')->user()->id,
            'correct_answer' => $fill_blank_activity->assessments()->where("is_correct_answer", 1)->first()->answer,
            'user_answer' => $request->answer,
            'is_correct' => $is_correct,
            'question_id' => $request->question_id,
        ]);
        $is_correct_activity = $fill_blank_activity->actiitiyUserAnswers()->where('question_id', $request->question_id)->where('user_id', auth('api')->id())->where('is_correct', 0)->count() ? false : true;
    //   info("is corect") ; 
    //   info($fill_blank_activity->actiitiyUserAnswers()->where('question_id', $request->question_id)->where('user_id', auth('api')->id())->where('is_correct', 0)->get()) ;
       
        $user_question_answer = UserQuestionAnswer::where([
            'activity_id' => $fill_blank_activity->id,
            'question_id' => $request->question_id,
            'user_id' => auth('api')->user()->id,
        ])->first();

        $user_answers_for_same_question = UserQuestionAnswer::where(['activity_id' => $fill_blank_activity->id, 'user_id' => auth('api')->user()->id])->pluck('trails');

        $activity_user = ActivityUser::where(['user_id' => auth('api')->user()->id,
            "activity_id" => $fill_blank_activity->id])->first();

        if ($user_question_answer) {
            $user_question_answer->update([
                "trails" => ++$user_question_answer->trails,
                'is_correct' => $is_correct_activity,
                'is_answered' => 1
            ]);


        } else {
            UserQuestionAnswer::create([
                'activity_id' => $fill_blank_activity->id,
                'question_id' => $request->question_id,
                'user_id' => auth('api')->user()->id,
                "trails" => 1,
                'is_correct' => $is_correct_activity,
                'is_answered' => 1
            ]);
        }
        $correct_answers_count = UserQuestionAnswer::where(['activity_id' => $fill_blank_activity->id,
            'user_id' => auth('api')->user()->id, 'is_correct' => true])->count();
        $answers_count = UserQuestionAnswer::where(['activity_id' => $fill_blank_activity->id,
            'user_id' => auth('api')->user()->id])->count();

        $answers_count_cycle = UserQuestionAnswer::where([
            'activity_id' => $fill_blank_activity->id,
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
//        activity score
//        flow score
        FlowUser::where(["flow_id" => $fill_blank_activity->flow->id,

            "user_id" => auth('api')->id()])->update([
//    assessment count
            'assessment_score' => ActivityUser::where(['user_id' => auth('api')->user()->id])->where(["status" => "finished"])->whereIn("activity_id", $fill_blank_activity->flow->activities()->where("type", "assessment")->pluck('id'))->count(),
// all activities count
            'score' => ActivityUser::whereIn('activity_id', $fill_blank_activity->flow->activities()->pluck('id'))->where("status", "finished")->count(),
//total flow activities
            'total_score' => $fill_blank_activity->flow->activities()->count(),

        ]);
    }
}

