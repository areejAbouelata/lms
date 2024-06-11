<?php

namespace App\Http\Requests\Api\Dashboard\Setting;


use App\Http\Requests\Api\ApiMasterRequest;

class SettingRequest extends ApiMasterRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'email' => "nullable|email",
            'whatsapp' => "nullable||string|max:250",
            'mobile' => "nullable||string|max:250",
            'work_time_start' => "nullable|string|max:250",
            'work_time_end' => "nullable|string|max:250",
            'work_day_start' => "nullable|string|max:250",
            'work_day_end' => "nullable|string|max:250",
            'address' => "nullable|string|max:250",
            "lat" => "nullable",
            "lng" => "nullable",
            "location" => "nullable|max:500",
            // contacts
            'how_it_works' => "nullable|string|max:250",

            'facebook' => "nullable|url",
            'twitter' => "nullable|url",
            'youtube' => "nullable|url",
            'instagram' => "nullable|url",
            'hr_mail' => "nullable|string",
            'dashboard_color' => 'nullable|string',
            'logo_image' => 'nullable|string',
        ];
    }

}
