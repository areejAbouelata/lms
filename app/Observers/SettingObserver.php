<?php

namespace App\Observers;

use App\Models\AppMedia;
use App\Models\Setting;

class SettingObserver
{
    public function saved(Setting $model)
    {
        if (request()->how_it_works) {
            logger('here1');
            if ($model->media()->exists()) {
                $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Setting', 'app_mediaable_id' => $model->id, 'media_type' => 'video'])->first();
                $image->delete();
                if (file_exists(storage_path('app/public/files/setting/' . $image->media))) {
                    \File::delete(storage_path('app/public/files/setting/' . $image->media));
                    $image->delete();
                }
            }
            $model->media()->create(['media' => request()->how_it_works, 'media_type' => 'video']);
        }

    }


}
