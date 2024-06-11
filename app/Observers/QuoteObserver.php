<?php

namespace App\Observers;

use App\Models\AppMedia;
use App\Models\Quote;

class QuoteObserver
{
    public function saved(Quote $model)
    {
        if (request()->image) {
            if ($model->media()->exists()) {
                $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Quote', 'app_mediaable_id' => $model->id, 'media_type' => 'image'])->first();
                $image->delete();
                if (file_exists(storage_path('app/public/images/quote/' . $image->media))) {
                    \File::delete(storage_path('app/public/images/quote/' . $image->media));
                    $image->delete();
                }
            }
            $model->media()->create(['media' => request()->image, 'media_type' => 'image']);
        }
    }

    public function deleted(Quote $model)
    {
        if ($model->media()->exists()) {
            $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Quote', 'app_mediaable_id' => $model->id, 'media_type' => 'image'])->first();
            if (file_exists(storage_path('app/public/images/quote/' . $image->media))) {
                \File::delete(storage_path('app/public/images/quote/' . $image->media));
            }
            $image->delete();
        }
    }

}
