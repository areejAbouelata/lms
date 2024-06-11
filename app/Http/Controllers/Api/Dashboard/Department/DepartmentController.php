<?php

namespace App\Http\Controllers\Api\Dashboard\Department;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Requests\Api\Dashboard\Department\DepartmentRequest;
use App\Http\Resources\Api\Dashboard\Department\DepartmentResource;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $department = Department::paginate(25);
        return DepartmentResource::collection($department)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }
  public function indexWithoutPagination(Request $request)
    {
        $department = Department::where('is_active' , 1)->get();
        return DepartmentResource::collection($department)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function show(Department $department)
    {
        return DepartmentResource::make($department)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function store(DepartmentRequest $request)
    {
        $department = Department::create($request->validated());
        return DepartmentResource::make($department)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());
        return DepartmentResource::make($department)->additional([
            'status' => "success",
            "message" => ""
        ]);
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard/admin.country.destroy')]);
    }
    public function toggleActive(Request $request)
    {
        Department::whereIn("id", $request->ids)->update(['is_active' => request()->is_active]);
        return response()->json(["status" => "success", "message" => trans('dashboard/admin.admin.updated')]);
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet
        ->setCellValue('A1', trans('dashboard.department.id'))
        ->setCellValue('B1', trans('dashboard.department.name'));
        $i = 2;
        foreach (Department::get() as $department) {
            
            $department_name = $department->translate('en')->name;
            $department_desc = $department->translate('en')->desc;
            $sheet->setCellValue('A' . $i, $department->id)
                ->setCellValue('B' . $i, $department_name);
            $i++;
        }

        $writer = new  Xlsx($spreadsheet);
        $name = "departments_sample" . time() . ".xlsx";
        $writer->save($name);
        $content = file_get_contents($name);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"');
        echo $content;
        unlink($name);
    }
}
