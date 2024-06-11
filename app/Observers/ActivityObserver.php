<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\AppMedia;

class ActivityObserver
{
    public function saved(Activity $model)
    {
        if (request()->attachment) {
            if (is_array(request()->attachment)) {
                $i = 0;
                foreach (request()->attachment as $attachment) {
                    $model->media()->create(['media' => $attachment, 'media_type' => request()->attachment_type[$i]]);
                    $i++;
                }
            } else {
                $model->media()->create(['media' => request()->attachment, 'media_type' => 'remark_file']);
            }

        }
        if (request()->image) {
            if ($model->media()->where("option", "cover")->exists()) {
                $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Activity', 'app_mediaable_id' => $model->id, 'media_type' => 'image', "option" => "cover"])->first();
                $image->delete();
                if (file_exists(storage_path('app/public/images/activity/' . $image->media))) {
                    \File::delete(storage_path('app/public/images/activity/' . $image->media));
                    $image->delete();
                }
            }
            $model->media()->create(['media' => request()->image, 'media_type' => 'image', "option" => "cover"]);
        }
    }

    public function deleted(Activity $model)
    {
        if ($model->media()->exists()) {
            $images = AppMedia::where(['app_mediaable_type' => 'App\Models\Activity', 'app_mediaable_id' => $model->id])->get();
            foreach ($images as $image) {
                if (file_exists(storage_path('app/public/' . $image->media_type . '/activity/' . $image->media))) {
                    \File::delete(storage_path('app/public/' . $image->media_type . '/activity/' . $image->media));
                }
                $image->delete();
            }
        }
    }

}
