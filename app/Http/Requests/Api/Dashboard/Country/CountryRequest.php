<?php

namespace App\Http\Requests\Api\Dashboard\Country;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Api\ApiMasterRequest;


class CountryRequest extends ApiMasterRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $country = $this->country ? $this->country->id : null;
        $rules = [
            'phone_code'              => 'nullable|numeric|digits_between:2,5|unique:countries,phone_code,' . $country,
            'image'                   => 'nullable|image',
            'phone_limit'             => 'nullable|numeric',
            // 'area'                    => 'nullable|array|min:4',
            // 'area.*'                  => 'array',
            // 'area.*.lat'              => 'numeric',
            // 'area.*.lng'              => 'numeric',
            'is_active'               => 'nullable|in:0,1',
            'continent'          => 'nullable|in:africa,europe,asia,south_america,north_america,australia',
            'short_name'                => 'nullable|string|min:2'
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name'] = 'required|string|between:3,250|unique:country_translations,name,' . $country . ',country_id';
            $rules[$locale . '.currenc`y'] = 'nullable|string|between:2,250';
            $rules[$locale . '.nationality'] = 'nullable|string|between:2,250';
            $rules[$locale . '.slug'] = 'nullable|string|between:2,250';
        }
        return $rules;
    }
}
