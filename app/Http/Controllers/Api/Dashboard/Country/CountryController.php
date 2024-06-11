<?php

namespace App\Http\Controllers\Api\Dashboard\Country;

use App\Models\{Country};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Requests\Api\Dashboard\Country\CountryRequest;
use App\Http\Resources\Api\Dashboard\{Country\CountryResource};

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::latest()->paginate(25);
        return CountryResource::collection($countries)->additional([
            'message' => '',
            'status' =>  'success'
        ]);
    }
    public function show($id)
    {
        $country = Country::findOrFail($id);
        return CountryResource::make($country)->additional(['status' => 'success', 'message' => '']);
    }

    public function indexWithoutPagination(Request $request)
    {
        $countries = Country::where('is_active' , 1)->latest()->get();
        return CountryResource::collection($countries)->additional([
            'message' => '',
            'status' =>  'success'
        ]);
    }
    public function store(CountryRequest $request)
    {
        $country = Country::create(array_except($request->validated() , ['image']));
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard.country.created')]);
    }
    public function update(CountryRequest $request, Country $country)
    {
        $country->update(array_except($request->validated() , ['image']));
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard.country.updated')]);
    }
    public function destroy(Country $country)
    {
        $country->delete();
        return response()->json(['status' => 'success', 'data' => null, 'message' =>  trans('dashboard.country.destroy')]);
    }
    public function toggleActive(Request $request)
    {
        Country::whereIn("id", $request->ids)->update(['is_active' => request()->is_active]);
        return response()->json(["status" => "success", "message" => trans('dashboard.admin.updated')]);
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet
        ->setCellValue('A1', trans('dashboard.country.id'))
        ->setCellValue('B1', trans('dashboard.country.name'));
        $i = 2;
        foreach (Country::get() as $country) {
            
            $country_name = $country->translate('en')->name;
            $country_slug = $country->translate('en')->slug;
            $country_currency = $country->translate('en')->currency;
            $country_nationality = $country->translate('en')->nationality;
            $sheet->setCellValue('A' . $i, $country->id)
                ->setCellValue('B' . $i, $country_name);
            $i++;
        }

        $writer = new  Xlsx($spreadsheet);
        $name = "countries_sample" . time() . ".xlsx";
        $writer->save($name);
        $content = file_get_contents($name);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($name) . '"');
        echo $content;
        unlink($name);
    }

}
