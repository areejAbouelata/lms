<?php

namespace App\Observers;

use App\Models\AppMedia;
use App\Models\Slider;

class SliderObserver
{
    public function saved(Slider $model)
    {
        if (request()->image) {
            if ($model->media()->exists()) {
                $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Slider', 'app_mediaable_id' => $model->id, 'media_type' => 'image'])->first();
                $image->delete();
                if (file_exists(storage_path('app/public/images/slider/' . $image->media))) {
                    \File::delete(storage_path('app/public/images/slider/' . $image->media));
                    $image->delete();
                }
            }
            $model->media()->create(['media' => request()->image, 'media_type' => 'image']);
        }
    }

    public function deleted(Slider $model)
    {
        if ($model->media()->exists()) {
            $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Slider', 'app_mediaable_id' => $model->id, 'media_type' => 'image'])->first();
            if (file_exists(storage_path('app/public/images/slider/' . $image->media))) {
                \File::delete(storage_path('app/public/images/slider/' . $image->media));
            }
            $image->delete();
        }
    }

}
