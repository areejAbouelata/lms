<?php

namespace App\Observers;

use App\Models\AppMedia;
use App\Models\Flow;

class FlowObserver
{
    public function saved(Flow $model)
    {
        if (request()->image) {
            if ($model->media()->exists()) {
                $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Flow', 'app_mediaable_id' => $model->id, 'media_type' => 'image'])->first();
                $image->delete();
                if (file_exists(storage_path('app/public/images/flow/' . $image->media))) {
                    \File::delete(storage_path('app/public/images/flow/' . $image->media));
                    $image->delete();
                }
            }
            $model->media()->create(['media' => request()->image, 'media_type' => 'image']);
        }
    }

    public function deleted(Flow $model)
    {
        if ($model->media()->exists()) {
            $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Flow', 'app_mediaable_id' => $model->id, 'media_type' => 'image'])->first();
            if (file_exists(storage_path('app/public/images/flow/' . $image->media))) {
                \File::delete(storage_path('app/public/images/flow/' . $image->media));
            }
            $image->delete();
        }
    }

}
