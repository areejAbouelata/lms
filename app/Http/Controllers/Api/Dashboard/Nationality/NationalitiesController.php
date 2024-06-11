<?php

namespace App\Http\Controllers\Api\Dashboard\Nationality;

use File;
use App\Models\User;
use App\Models\Nationality;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Requests\Api\Dashboard\Admin\AdminRequest;
use App\Http\Resources\Api\Dashboard\Auth\AdminResource;
use App\Http\Resources\Api\Dashboard\Nationality\NationalityResource;

class NationalitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // $nationalities = Nationality::all();
        // return [
        //     "data" => NationalityResource::collection($nationalities),
        //     "status" => "success",
        //     "message" => "",
        // ];
        
        $nationalities = Nationality::join('nationality_translations', 'nationalities.id', '=', 'nationality_translations.nationality_id')
      ->where('nationality_translations.locale'  , 'en')  
      ->select('nationality_translations.*','nationalities.id')->distinct('nationality_translations.name')
      ->orderBy('nationality_translations.name' , 'ASC')
        ->get();
       
    // return  $nationalities ;
        return NationalityResource::collection($nationalities)->additional([
            "status" => "success",
            "message" => "",
        ]);
    }

    public function indexWithPaginte()
    {
        $nationalities = Nationality::join('nationality_translations', 'nationalities.id', '=', 'nationality_translations.nationality_id')
      ->where('nationality_translations.locale'  , 'en')  
      ->select('nationality_translations.*','nationalities.id')->distinct('nationality_translations.name')
      ->orderBy('nationality_translations.name' , 'ASC')
        ->get();
       
    // return  $nationalities ;
        return NationalityResource::collection($nationalities)->additional([
            "status" => "success",
            "message" => "",
        ]);
    }

    public function codes()
    {
        $json = File::get('../database/seeders/phone_codes.json');
        return $countries = json_decode($json);
    }
    
    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet
        ->setCellValue('A1', trans('dashboard.nationality.id'))
        ->setCellValue('B1', trans('dashboard.nationality.name'));
        $i = 2;
        foreach (Nationality::get() as $nationality) {
            
            $nationality_name = @$nationality->translate('en')->name ?? @$nationality->translate('ar')->name;
            $sheet->setCellValue('A' . $i, $nationality->id)
                ->setCellValue('B' . $i, $nationality_name);
            $i++;
        }

        $writer = new  Xlsx($spreadsheet);
        $name = "nationalities_sample" . time() . ".xlsx";
        $writer->save($name);
        $content = file_get_contents($name);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"');
        echo $content;
        unlink($name);
    }
    
}
