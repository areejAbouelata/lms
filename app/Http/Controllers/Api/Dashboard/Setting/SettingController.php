<?php

namespace App\Http\Controllers\Api\Dashboard\Setting;

use App\Models\AppMedia;
use Exception;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Setting\SettingRequest;
use App\Http\Resources\Api\Dashboard\Setting\SettingResource;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $settings = Setting::latest()->get();

        return SettingResource::collection($settings)->additional([
            'status' => 'success', 'message' => '',
        ]);
    }

    public function store(SettingRequest $request)
    {
        DB::beginTransaction();
        try {
            $setting = Setting::latest()->get();
            $inputs = $request->validated();
            foreach ($inputs as $key => $value) {
                Setting::updateOrCreate(['key' => trim($key)], ['value' => $value]);
                if ($request->logo_image) {
                    $logo_image = Setting::where('key', 'logo_image')->first();
                    $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Users', 'app_mediaable_id' => $logo_image?->id, 'media_type' => 'image'])->first();
                    if ($image?->media != request()->image) {
                        $image?->delete();
                    }
                    $logo_image?->media()->delete();
                    $res = $logo_image?->media()->create(['media' => request()->logo_image, 'media_type' => 'image']);
                }
            }
            DB::commit();
            return (SettingResource::collection($setting->fresh()))->additional(['status' => 'success', 'message' => trans('app/driver.auth.personal_info_added_successfully')]);
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
            info($e->getMessage());
            return response()->json(['status' => 'fail', 'data' => null, 'message' => trans('app/driver.auth.not_registered_try_again')], 422);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    // public function update(CategoryRequest $request, $id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $user = RestaurantCategory::findOrFail($id);
    //         $user->update(array_except($request->validated(), $request->image ) );
    //         $this->uploadMedia($user, $request->image, 'restaurant_categories', 'app_media');
    //         DB::commit();
    //         return (new CategoryResource($user))->additional(['status' => 'success', 'message' => trans('app/driver.auth.personal_info_added_successfully')]);
    //     } catch (Exception $e) {
    //         DB::rollback();
    //         dd($e);
    //         info($e->getMessage());
    //         return response()->json(['status' => 'fail', 'data' => null, 'message' => trans('app/driver.auth.not_registered_try_again')], 422);
    //     }
    // }

    // public function uploadMedia($model, $media, $option, $table = 'app_media')
    // {
    //     if ($table == 'app_media') {
    //         $model->media()->updateOrCreate(['option' => $option], [
    //             'media'      => $media,
    //             'media_type' => 'image',
    //             'option'     => $option
    //         ]);
    //         return true;
    //     }
    //     return false;
    // }
}
