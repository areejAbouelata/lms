<?php

namespace App\Observers;

use App\Models\AppMedia;
use App\Models\Group;
use Illuminate\Support\Facades\File;

class GroupObserver
{
    public function saved(Group $group)
    {
        if (request()->image) {
            $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Group','app_mediaable_id' => $group->id ,'media_type' => 'image'])->first();
            if ($image?->media != request()->image)
            {
                $image?->delete();
                $group->media()->create(['media' => request()->image, 'media_type' => 'image']);
            }
        }
    }

    public function deleted(Group $group)
    {
        if ($group->media()->exists()) {
            $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Group','app_mediaable_id' => $group->id ,'media_type' => 'image'])->first();
            if (file_exists(storage_path('app/public/images/group/'.$image->media))){
                File::delete(storage_path('app/public/images/group/'.$image->media));
            }
            $image->delete();
        }
    }
}
