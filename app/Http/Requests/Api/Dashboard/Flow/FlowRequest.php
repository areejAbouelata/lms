<?php

namespace App\Http\Requests\Api\Dashboard\Flow;

use Illuminate\Foundation\Http\FormRequest;

class FlowRequest extends FormRequest
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
        $flow = $this->flow ? $this->flow->id : null;
        $status = $this->flow? 'nullable': 'required';
        $rules = [
            'image' => "string|nullable" ,
            "is_active" => "$status|in:0,1"
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name'] = 'required|string|between:3,250';
            $rules[$locale . '.desc'] = 'required|string|between:2,250';
        }
        return $rules;
    }
}
