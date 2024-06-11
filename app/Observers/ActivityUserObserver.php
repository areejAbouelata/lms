<?php

namespace App\Observers;

use App\Models\ActivityUser;
use App\Models\AppMedia;

class ActivityUserObserver
{

    public function saved(ActivityUser $user)
    {

        if (request()->attachment ) {
            $image = AppMedia::where(['app_mediaable_type' => 'App\Models\ActivityUser','app_mediaable_id' => $user->id ,'media_type' => 'file'])->first();
            if ($image?->media != request()->attachment)
            {
                $image?->delete();
                // $user->media()->create(['media' => request()->image, 'media_type' => 'image']);
            }
            $res=  $user->media()->create(['media' => request()->attachment, 'media_type' => 'file']);

        }
    }

    public function deleted(ActivityUser $user)
    {
        if ($user->media()->exists()) {
            $image = AppMedia::where(['app_mediaable_type' => 'App\Models\ActivityUser','app_mediaable_id' => $user->id ,'media_type' => 'image'])->first();
            if ($image && file_exists(storage_path('app/public/files/activity_users/'.$image->media))){
                File::delete(storage_path('app/public/files/activity_users/'.$image->media));
            }
            $image->delete();
        }
    }
}
