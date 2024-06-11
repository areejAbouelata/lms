<?php

namespace App\Http\Controllers\Api\Dashboard\Client;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Flow;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ClientFlowExcelController extends Controller
{
    public function export()
    {
        $users = User::select('full_name', 'email', 'id')->has('flows')->where("user_type", "client")->get();

        $data = $users->map(function ($user) {
            $flow = $user->flows()->orderByDesc("flow_users.created_at")->select(['flows.*', 'flow_users.created_at as enrollment_date'])->first();

            $activity = $flow->activities()->select('activity_users.finished_at as complete_date', 'activity_users.end_date as  expected_complete_date')
                ->join('activity_users', 'activities.id', 'activity_users.activity_id')->where('activity_users.user_id', $user->id)
                ->orderBy('activities.id', 'desc')->first();

            $overallCompleteAll = $flow->activities()->join('activity_users', 'activities.id', 'activity_users.activity_id')
                ->where('activity_users.user_id', $user->id)->count();

            $overallFinished = $flow->activities()->join('activity_users', 'activities.id', 'activity_users.activity_id')
                ->where('activity_users.user_id', $user->id)->where('status', 'finished')->count();
            $all_activities = Activity::where(['flow_id' => $flow->id, 'type' => "assessment"])->sum('total_score');
            $user_activities = Flow::find($flow->id)->allActivityUsers()->
            whereHas('activity', function ($q) {
                $q->where('type', 'assessment');
            })
                ->where('user_id', $user->id)->sum('score');
            $all_assessments_score = $all_activities>0 ? ((int) $user_activities / (int )$all_activities * 100) : 0;

            // $activitiesAssessment =  $flow->activities()->join('activity_users', 'activities.id', 'activity_users.activity_id')
            //         ->where('activity_users.user_id', $user->id)->where('activities.type', '=', 'assessment')->count();
            //         $activitiesAssessmentCorrect =  $flow->activities()->join('activity_users', 'activities.id', 'activity_users.activity_id')
            //         ->join('activity_answer_users', 'activities.id', 'activity_answer_users.activity_id')
            //         ->where('activity_users.user_id', $user->id)->where('activities.type', '=', 'assessment')->where('activity_answer_users.is_correct' ,'=',1)->count();

            //         dd($activitiesAssessment,$activitiesAssessmentCorrect);

            return [
                "full_name" => $user->full_name,
                "email" => $user->email,
                "enrollment_date" => $flow?->enrollment_date,
                "flow" => $flow->translate('en')->name,
                "expected_complete_date" =>  $flow?->allActivityUsers()->where(['user_id' => $user->id])->max('end_date'),
                "complete_date" => $activity?->complete_date,
                "overall_complete" => $overallFinished ? round(($overallFinished / $overallCompleteAll) * 100, 2) . " %" : 0,
                'all_assessments_score' => round( $all_assessments_score ,  2 ). " %"
            ];
        });


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet
            ->setCellValue('A1', trans('dashboard.client.full_name'))
            ->setCellValue('B1', trans('dashboard.client.email'))
            ->setCellValue('C1', trans('dashboard.client.flow'))
            ->setCellValue('D1', trans('dashboard.client.enrollment_date'))
            ->setCellValue('E1', trans('dashboard.client.expected_complete_date'))
            ->setCellValue('F1', trans('dashboard.client.complete_date'))
            ->setCellValue('G1', trans('dashboard.client.overall_completion'))
            ->setCellValue('H1', trans('dashboard.client.assignments_average_score'));

        $i = 2;

        foreach ($data->toArray() as $item) {
            $sheet->setCellValue('A' . $i, $item['full_name'])
                ->setCellValue('B' . $i, $item['email'])
                ->setCellValue('C' . $i, $item['flow'])
                ->setCellValue('D' . $i, $item['enrollment_date'])
                ->setCellValue('E' . $i, $item['expected_complete_date'])
                ->setCellValue('F' . $i, $item['complete_date'])
                ->setCellValue('G' . $i, $item['overall_complete'])
                // ->setCellValue('F' . $i, $user['assignments_average_score']);
                ->setCellValue('H' . $i, $item['all_assessments_score']);
            $i++;
        }

        $writer = new  Xlsx($spreadsheet);
        $name = "clients_" . date('Y-m-d') . ".xlsx";
        $writer->save($name);
        $content = file_get_contents($name);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"');
        echo $content;  // this actually send the file content to the browser
        unlink($name);
    }
}
