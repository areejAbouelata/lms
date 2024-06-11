<?php

namespace App\Observers;

use App\Models\AppMedia;
use App\Models\User;
use Illuminate\Support\Facades\File;

class UserObserver
{
    public function saved(User $user)
    {

        if (request()->image) {
            $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Users', 'app_mediaable_id' => $user->id, 'media_type' => 'image'])->first();
            if ($image?->media != request()->image) {
                $image?->delete();
                // $user->media()->create(['media' => request()->image, 'media_type' => 'image']);
            }
            $user->media()->delete();
            $res = $user->media()->create(['media' => request()->image, 'media_type' => 'image']);

        }
    }

    public function deleted(User $user)
    {
        if ($user->media()->exists()) {
            $image = AppMedia::where(['app_mediaable_type' => 'App\Models\Users', 'app_mediaable_id' => $user->id, 'media_type' => 'image'])->first();
            if ($image && file_exists(storage_path('app/public/images/categories/' . $image->media))) {
                File::delete(storage_path('app/public/images/categories/' . $image->media));
            }
            $image->delete();
        }
    }
}
