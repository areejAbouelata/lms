<?php

namespace App\Http\Controllers\Api\Dashboard\EvalutionForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{EvalutionForm};
use App\Http\Resources\Api\Dashboard\{EvalutionForm\EvalutionFormResource};
use App\Http\Requests\Api\Dashboard\EvalutionForm\EvalutionFormRequest;

class EvalutionFormController extends Controller
{
    public function index(Request $request)
    {
        $forms = EvalutionForm::latest()->paginate(25);
        return EvalutionFormResource::collection($forms)->additional([
            'message' => '',
            'status' =>  'success'
        ]);
    }
    public function show($id)
    {
        $form = EvalutionForm::findOrFail($id);
        return EvalutionFormResource::make($form)->additional(['status' => 'success', 'message' => '']);
    }

    public function indexWithoutPagination(Request $request)
    {
        $forms = EvalutionForm::where('is_active' , 1)->latest()->get();
        return EvalutionFormResource::collection($forms)->additional([
            'message' => '',
            'status' =>  'success'
        ]);
    }
    public function store(EvalutionFormRequest $request)
    {
        $form = EvalutionForm::create($request->validated());
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard.form.created')]);
    }
    public function update(EvalutionFormRequest $request, EvalutionForm $form)
    {
        $form->update($request->validated());
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard.form.updated')]);
    }
    public function destroy(EvalutionForm $form)
    {
        $form->delete();
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard.form.destroy')]);
    }
    public function toggleActive(Request $request)
    {
        EvalutionForm::whereIn("id", $request->ids)->update(['is_active' => request()->is_active]);
        return response()->json(["status" => "success", "message" => trans('dashboard.admin.updated')]);
    }

}
