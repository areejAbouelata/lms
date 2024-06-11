<?php

namespace App\Http\Controllers\Api\Dashboard\Activity;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityUser;
use App\Models\Flow;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ActivityExportController extends Controller
{
    public function export(Flow $flow)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet
            ->setCellValue('A1', trans('dashboard.activity.Step Name'))
            ->setCellValue('B1', trans('dashboard.activity.Type'))
            ->setCellValue('C1', trans('dashboard.activity.Timing'))
            ->setCellValue('D1', trans('dashboard.activity.Enrollments'))
            ->setCellValue('E1', trans('dashboard.activity.Completion Rate'))
            ->setCellValue('F1', trans('dashboard.activity.Average Score'));
        $i = 2;
        foreach (Activity::where('flow_id', $flow->id)->get() as $activity) {

            $activity_name = $activity->translate('en')->desc;
            $activity_type = null;
            switch ($activity->type) {
                case 'task':
                    $activity_type = 'Task';
                    break;
                case 'video':
                    $activity_type = 'Video';
                    break;
                case 'audio':
                    $activity_type = 'Audio';
                    break;
                case 'assessment':
                    $activity_type = 'Assessment';
                    break;
                case 'html_content':
                    $activity_type = 'Html Content';
                    break;

            }
            $score_avrage = "";
            if ($activity->type == "assessment") {
                $score_avrage = ActivityUser::where('activity_id', $activity->id)->where('status', 'finished')->count() ? (ActivityUser::where('activity_id', $activity->id)->where('status', 'finished')->count() / ActivityUser::where('activity_id', $activity->id)->count() * 100).'%' : '0%';
            }
            $sheet->setCellValue('A' . $i, $activity_name)
                ->setCellValue('B' . $i, $activity_type)
                ->setCellValue('C' . $i, $activity->duration .'-'. $activity->duration_type)
                ->setCellValue('D' . $i, ActivityUser::where('activity_id', $activity->id)->count())
                ->setCellValue('E' . $i, ActivityUser::where('activity_id', $activity->id)->where('status', 'finished')->count() ? (ActivityUser::where('activity_id', $activity->id)->where('status', 'finished')->count() / ActivityUser::where('activity_id', $activity->id)->count() * 100).'%' : '%')
                ->setCellValue('F' . $i, $score_avrage);
            $i++;
        }

        $writer = new  Xlsx($spreadsheet);
        $name = "flow_steps_sample" . time() . ".xlsx";
        $writer->save($name);
        $content = file_get_contents($name);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"');
        echo $content;
        unlink($name);
    }

}
