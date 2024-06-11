<?php

namespace App\Models;

use App\Observers\SettingObserver;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = ['id','created_at','updated_at'];
    protected static function boot()
    {
        parent::boot();
        Setting::observe(SettingObserver::class);
    }

    public function getHowItWorksAttribute()
    {
        $image = $this->media()->exists() ? 'storage/files/settings/' . $this->media()->first()->media : 'dashboardAssets/files/cover/cover_sm.png';
        return asset($image);
    }

    public function media()
    {
        return $this->morphOne(AppMedia::class, 'app_mediaable');
    }
}
