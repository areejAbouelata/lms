<?php

namespace App\Http\Requests\Api\Dashboard\JobTitle;

use Illuminate\Foundation\Http\FormRequest;

class JobTitleRequest extends FormRequest
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
        $job_title = $this->job_title ? $this->job_title->id : null;
        $rules = [];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.title'] = 'required|string|between:3,250|unique:job_title_translations,title,' . $job_title . ',job_title_id';
        }
        return $rules;
    }
}
