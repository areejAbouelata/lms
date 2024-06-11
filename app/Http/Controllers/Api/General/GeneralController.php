<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\General\AttachmentRequest;
use App\Services\UploadFileService;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function attachment(AttachmentRequest $request)
    {
        $name = null;
        try {
            if ($request->file) {
                if ($request->attachment_type == 'image') {
                    $name = UploadFileService::uploadImg($request->file, $request->model);
                } elseif ($request->attachment_type == "file") {
                    $name = UploadFileService::uploadFile($request->file, $request->model);

                }
            }
            return \response()->json([
                'message' => trans('app/general.messages.uploaded_successfully'),
                'status' => 'success',
                'data' => $name,
            ]);
        } catch (\Exception $e) {
            info($e);
            return response()->json(['status' => 'fail', 'message' => trans('app/client.messages.order.something_went_wrong_please_try_again'), 'data' => null], 500);
        }
    }
}
