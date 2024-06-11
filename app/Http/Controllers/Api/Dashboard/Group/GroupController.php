<?php

namespace App\Http\Controllers\Api\Dashboard\Group;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Services\GroupServices;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Requests\Api\Dashboard\Group\GroupRequest;
use App\Http\Resources\Api\Dashboard\Group\{GroupResource, GroupSimpleResource};

class GroupController extends Controller
{
    protected $groupServices;

    public function __construct(GroupServices $groupServices)
    {
        $this->groupServices = $groupServices;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $groups = Group::when(request()->keyword, function ($query) {
            $query->orWhereTranslationLike('name', '%' . request()->keyword . '%')
                ->orWhereTranslationLike('desc', '%' . request()->keyword . '%');
        })->latest()->when(is_numeric(request()->paginate), function ($query) {
            return $query->paginate(request()->paginate);
        }, function ($query) {
            return $query->get();
        });

        if (is_numeric(request()->paginate)) {
            return GroupResource::collection($groups)->additional(['status' => 'success', 'message' => '']);
        } else {
            return GroupSimpleResource::collection($groups)->additional(['status' => 'success', 'message' => '']);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(GroupRequest $request)
    {
        $group = Group::create(array_except($request->validated(), ['image']));
        if ($request->user_ids)
            $this->groupServices->assignUsersToGroup($request->user_ids, $group->id);
        return GroupResource::make($group)->additional(['status' => 'success', 'message' => trans('dashboard/admin.admin.created')]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $group = Group::findOrFail($id);
        return GroupResource::make($group)->additional(['status' => 'success', 'message' => '']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $group = Group::findOrFail($id);

        if ($group->delete()) {
            return response()->json(['status' => 'success', 'data' => null, 'message' => trans('dashboard/admin.admin.deleted')]);
        }
    }

    public function toggleActive($id)
    {
        $group = Group::findOrFail($id);
        $group->update(['is_active' => request()->is_active]);
        return GroupResource::make($group->fresh())->additional(["status" => "success", "message" => trans('dashboard/admin.admin.updated')]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(GroupRequest $request, $id)
    {
        $group = Group::findOrFail($id);
        $group->update(array_except($request->validated(), ['image']));
        if ($request->user_ids)
            $this->groupServices->updateAssignUsersToGroup($request->user_ids, $group->id);
        else
            $this->groupServices->unassignUsersToGroup($group->id);
        return GroupResource::make($group)->additional(['status' => 'success', 'message' => trans('dashboard/admin.admin.updated')]);
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet
        ->setCellValue('A1', trans('dashboard.group.id'))
        ->setCellValue('B1', trans('dashboard.group.name'));
        $i = 2;
        foreach (Group::get() as $group) {
            
            $group_name = $group->translate('en')->name;
            $group_desc = $group->translate('en')->desc;
            $sheet->setCellValue('A' . $i, $group->id)
                ->setCellValue('B' . $i, $group_name);
            $i++;
        }

        $writer = new  Xlsx($spreadsheet);
        $name = "group_sample" . time() . ".xlsx";
        $writer->save($name);
        $content = file_get_contents($name);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"');
        echo $content;
        unlink($name);
    }

}
