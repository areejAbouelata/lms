<?php

namespace App\Http\Controllers\Api\Dashboard\Flow;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityUser;
use App\Models\Flow;
use App\Models\FlowUser;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ExportFlowController extends Controller
{
    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet
            ->setCellValue('A1', trans('dashboard.flow.Flow Name'))
            ->setCellValue('B1', trans('dashboard.flow.Created On'))
            ->setCellValue('C1', trans('dashboard.flow.Status'))
            ->setCellValue('D1', trans('dashboard.flow.Enrollments'))
            ->setCellValue('E1', trans('dashboard.flow.Completion Rate'))
            ->setCellValue('F1', trans('dashboard.flow.Average Score'))
            ->setCellValue('G1', trans('dashboard.flow.Steps'))
            ->setCellValue('H1', trans('dashboard.flow.Tasks'))
            ->setCellValue('I1', trans('dashboard.flow.Videos'))
            ->setCellValue('J1', trans('dashboard.flow.Audio'))
            ->setCellValue('K1', trans('dashboard.flow.Assignment'))
            ->setCellValue('L1', trans('dashboard.flow.HTML'));
        $i = 2;
        foreach (Flow::get() as $flow) {
            $activities = $flow->allActivityUsers();
            $sum_activities = $flow->allActivityUsers()->whereHas('activity', function ($q) {
                $q->where('type', 'assessment');
            })->sum('score');

            $count = $flow->allActivityUsers()->whereHas('activity', function ($q) {
                $q->where('type', 'assessment');
            })->count();
            $avrage = $count > 0 ? $sum_activities / $count : 0;
            $flow_name = $flow->translate('en')->name;
            $sheet->setCellValue('A' . $i, $flow_name)
                ->setCellValue('B' . $i, $flow->created_at->toDateString())
                ->setCellValue('C' . $i, $flow->is_active ? "Active" : "Inactive")
                ->setCellValue('D' . $i, $flow->flowUsers()->count())
                ->setCellValue('E' . $i, $activities->where('status', 'finished')->count() ? ($activities->where('status', 'finished')->count() / $activities->count() * 100) . '%' : '0%')
                ->setCellValue('F' . $i, $avrage . '%')
                ->setCellValue('G' . $i, $flow->activities()->count())
                ->setCellValue('H' . $i, $flow->activities()->where('type', 'task')->count())
                ->setCellValue('I' . $i, $flow->activities()->where('type', 'video')->count())
                ->setCellValue('J' . $i, $flow->activities()->where('type', 'audio')->count())
                ->setCellValue('K' . $i, $flow->activities()->where('type', 'assessment')->count())
                ->setCellValue('L' . $i, $flow->activities()->where('type', 'html_content')->count());
            $i++;
        }

        $writer = new  Xlsx($spreadsheet);
        $name = "flows_sample" . time() . ".xlsx";
        $writer->save($name);
        $content = file_get_contents($name);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"');
        echo $content;
        unlink($name);
    }


    public function flowUsers(Flow $flow)
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet
            ->setCellValue('A1', trans('dashboard.activity.User'))
            ->setCellValue('B1', trans('dashboard.activity.Email'))
            ->setCellValue('C1', trans('dashboard.activity.Overall Completion'))
            ->setCellValue('D1', trans('dashboard.activity.Enrollment Date'))
            ->setCellValue('E1', trans('dashboard.activity.Expected Completion Date'))
            ->setCellValue('F1', trans('dashboard.activity.Completion Date'));
        $letters = ['G1', 'H1', 'I1', 'J1', 'K1'
            , 'L1', 'M1', 'N1', 'O1', 'P1'
            , 'Q1', 'R1', 'S1', 'T1', 'U1'
            , 'V1', 'W1', 'X1', 'Y1', 'Z1'
            , 'AA1'
        ];
        $letters_only = ['G', 'H', 'I', 'J', 'K'
            , 'L', 'M', 'N', 'O', 'P'
            , 'Q', 'R', 'S', 'T', 'U'
            , 'V', 'W', 'X', 'Y', 'Z'
            , 'AA'
        ];
        $letter_index = 0;
        $i = 2;
        $activities_ids = [];
        $all_activities_ids = [];
        foreach (Activity::where('flow_id', $flow->id)->get() as $activity) {
            // dd($letters[$letter_index]) ;
            $all_activities_ids[] = $activity->id;
            foreach ($activity->users as $user) {
                $activities_ids[$user->id] = $activity->id;
            }
            $name = $activity->translate('en')->desc;
            $sheet->setCellValue($letters[$letter_index], $name);
            $letter_index++;
        }
        $letters_counter = 0;
        foreach ($flow->users as $user) {
            $completion_rate = $flow->allActivityUsers()->where(['user_id' => $user->id, 'status' => 'finished'])->count() ? $flow->allActivityUsers()->where(['user_id' => $user->id, 'status' => 'finished'])->count() / $flow->allActivityUsers()->where(['user_id' => $user->id])->count() * 100 : 0;
            $enrollment_date =  FlowUser::where(['user_id' => $user->id, 'flow_id' => $flow->id])->first()?->assigned_at ? Carbon::parse(FlowUser::where(['user_id' => $user->id, 'flow_id' => $flow->id])->first()?->assigned_at)?->toDateTimeString() :FlowUser::where(['user_id' => $user->id, 'flow_id' => $flow->id])->first()?->created_at?->toDateTimeString();
            $complete_date = $flow->allActivityUsers()->where(['user_id' => $user->id])->latest()->first()->finished_at ? $flow->allActivityUsers()->where(['user_id' => $user->id])->latest()->first()->finished_at?->toDateTimeString() : NULL;
            $expected_date = $flow->allActivityUsers()->where(['user_id' => $user->id])->max('end_date');
            $sheet->
            setCellValue('A' . $i, $user->full_name)
                ->setCellValue('B' . $i, $user->email)
                ->setCellValue('C' . $i, $completion_rate . " %")
                ->setCellValue('D' . $i, $enrollment_date)
                ->setCellValue('E' . $i, $expected_date)
                ->setCellValue('F' . $i, $complete_date);
            $letters_counter = 0;
            foreach ($all_activities_ids as $activity_id) {

                $activity = Activity::find($activity_id);
                if ($activity?->type == "assessment") {
                    $sheet->setCellValue($letters_only[$letters_counter] . $i,
                        $activity->total_score > 0 ? (ActivityUser::where(['user_id' => $user->id, 'activity_id' => $activity_id])->first()?->score / $activity->total_score * 100) . "  %" : 0);

                } else {
                    $sheet->setCellValue($letters_only[$letters_counter] . $i,
                        ActivityUser::where(['user_id' => $user->id, 'activity_id' => $activity_id])->first()?->status == 'pending' ? 'FALSE' : 'TRUE');
                }
                $letters_counter++;

            }

            $i++;
        }
        $writer = new  Xlsx($spreadsheet);
        $name = "flow_users" . time() . ".xlsx";
        $writer->save($name);
        $content = file_get_contents($name);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"');
        echo $content;
        unlink($name);
    }

}
