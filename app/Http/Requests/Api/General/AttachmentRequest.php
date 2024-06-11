<?php

namespace App\Http\Requests\Api\General;

use App\Http\Requests\Api\ApiMasterRequest;

class AttachmentRequest extends ApiMasterRequest
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
        return [
            'file'            => 'required|file',
            'files'           => 'required_if:file,NULL|array',
            'files.*'         => 'nullable|file',
            'attachment_type' => 'required|in:image,file,audio,video',
            'model'           => 'required|in:users,departments,activities,flows,groups,settings'
        ];
    }
}
