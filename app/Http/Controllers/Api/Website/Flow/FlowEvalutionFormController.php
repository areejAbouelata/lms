<?php

namespace App\Http\Controllers\Api\Website\Flow;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Website\Flow\FlowActivityResource;
use App\Http\Resources\Api\Website\Flow\FlowResource;
use Illuminate\Http\Request;
use App\Models\{UserEvaluation,EvalutionForm ,FlowUser};
use App\Http\Requests\Api\Website\EvalutionForm\EvalutionFormRequest;

class FlowEvalutionFormController extends Controller
{
    

    public function store( EvalutionFormRequest $request )
    {
        $flow =  auth('api')->user()->flows()->findOrFail($request->flow_id);
        
        foreach($request->evalution_answers as $answer){
            // dd($answer['evalution_form_answer']);
            $form = EvalutionForm::findOrFail($answer['evalution_form_id']);
            $user_evalution = UserEvaluation::create([
                                                        'flow_id'               => $request->flow_id , 
                                                        'user_id'               => auth('api')->id(),
                                                        'evalution_form_id'     => @$answer['evalution_form_id'] ,
                                                        'evalution_form_answer' => @$answer['evalution_form_answer'] ,
                                                        'evalution_form_data'   => @$form->toJson() ,
                                                     ]);
        }
        
        $user_flow = FlowUser::where(['user_id'=> auth('api')->id() , 'flow_id'=> $request->flow_id])->first();
        if($user_flow){
            $user_flow->update(['rate'=>$request->rate]);
        }

        return FlowActivityResource::make($flow)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }
}
