<?php

namespace App\Http\Controllers\Api\Dashboard\Client;

use App\Http\Controllers\Controller;
use App\Http\Services\ClientServices;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Department;
use App\Models\DepartmentTranslation;
use App\Models\Group;
use App\Models\GroupTranslation;
use App\Models\Nationality;
use App\Models\NationalityTranslation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Exception;

class ClientByExcelController extends Controller
{
    public $services;

    public function __construct(ClientServices $services)
    {
        $this->services = $services;
    }

    public function export()
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet
            ->setCellValue('A1', trans('dashboard.client.full_name'))
            ->setCellValue('B1', trans('dashboard.client.first_name'))
            ->setCellValue('C1', trans('dashboard.client.last_name'))
            ->setCellValue('D1', trans('dashboard.client.phone'))
            ->setCellValue('E1', trans('dashboard.client.phone_code'))
            ->setCellValue('F1', trans('dashboard.client.email'))
            ->setCellValue('G1', trans('dashboard.client.gender'))
            ->setCellValue('H1', trans('dashboard.client.is_active'))
            ->setCellValue('I1', trans('dashboard.client.password'))
            ->setCellValue('J1', trans('dashboard.client.job_title'))
            ->setCellValue('K1', trans('dashboard.client.department_id'))
            ->setCellValue('L1', trans('dashboard.client.group_id'))
            ->setCellValue('M1', trans('dashboard.client.country_id'))
            ->setCellValue('N1', trans('dashboard.client.nationality_id'))
            ->setCellValue('O1', trans('dashboard.client.hire_date'))
            ->setCellValue('P1', trans('dashboard.client.direct_manager'))
            ->setCellValue('Q1', trans('dashboard.client.age'))
            ->setCellValue('R1', trans('dashboard.client.address'));
        $writer = new  Xlsx($spreadsheet);
        $writer->save("clients_sample.xlsx");
        $content = file_get_contents("clients_sample.xlsx");

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode("clients_sample.xlsx") . '"');
        echo $content;  // this actually send the file content to the browser
        unlink("clients_sample.xlsx");
    }

    public function import(Request $request)
    {
//        file , flow_id
        try {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($request->users_file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            if (!empty($sheetData)) {
                for ($i = 1; $i < count($sheetData); $i++) {
//                dump($sheetData[$i]);
//                dump($this->checkIfCanNotCreateProduct($sheetData[$i]));
                    if ($this->checkIfCanNotCreateProduct($sheetData[$i])) continue;
                    $user_data = [
                        "full_name" => $sheetData[$i][0],
                        "first_name" => $sheetData[$i][1],
                        "last_name" => $sheetData[$i][2],
                        "phone" => $sheetData[$i][3],
                        "phone_code" => $sheetData[$i][4],
                        "email" => $sheetData[$i][5],
                        "gender" => $sheetData[$i][6],
                        "is_active" => $sheetData[$i][7],
                        "password" => $sheetData[$i][8],
                        "job_title" => $sheetData[$i][9],
                        "department_id" => $this->getDepartmentId($sheetData[$i][10]),  // get
                        "group_id" => $this->getGroupId($sheetData[$i][11]),  // get
                        "country_id" => $this->getCountryId($sheetData[$i][12]),  // get
                        "nationality_id" => $this->getNationalityId($sheetData[$i][13]),  // get
                        "hire_date" => Carbon::parse($sheetData[$i][14]),  // get
                        "direct_manager" => $sheetData[$i][15],  // get
                        "age" => $sheetData[$i][16],  // get
                        "address" => $sheetData[$i][17],  // get
                        "user_type" => "client"
                    ];
                    $user = User::create($user_data);
//                    TODO Add to queue

                    $this->services->sendMailUsingExcel($user_data);
//            TODO        assign user to flow
                    $this->services->assignUsersToFlow([$user->id], $request->flow_id);
                }
            }
            return response()->json(['message' => 'success', 'data' => null]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'fail'
                , 'status' => 'check your sheet'
            ], 422);
        } catch (QueryException $exception) {
            info($exception);
            return response()->json([
                'message' => 'check your sheet'
                , 'status' => 'fail'
            ], 422);

        }
    }

    public function checkIfCanNotCreateProduct($record)
    {
        for ($i = 0; $i < 17; $i++) {
//            dump($record[$i]) ;
            if ($record[$i] === null) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getDepartmentId($key_word)
    {
        if ($key_word != null) {
            // $category = DepartmentTranslation::where('name', 'like', '%'.$key_word.'%')->first();
            $category = Department::where('id', $key_word)->first();
            return $category ? $category->id : false;
        } else {
            $category = null;
            return $category;
        }
    }

    public function getGroupId($key_word)
    {
        if ($key_word != null) {
            // $category = CategoryTranslation::where('name', 'like', '%'.$key_word.'%')->first();
            $category = Group::where('id', $key_word)->first();
            return $category ? $category->id : false;
        } else {
            $category = null;
            return $category;
        }
    }

    public function getCountryId($key_word)
    {
        if ($key_word != null) {
            $category = Country::where('id', $key_word)->first();
            return $category ? $category->id : false;
        } else {
            $category = null;
            return $category;
        }
    }

    public function getNationalityId($key_word)
    {
        if ($key_word != null) {
            $category = Nationality::where('id', $key_word)->first();
            return $category ? $category->id : false;
        } else {
            $category = null;
            return $category;
        }
    }

}
