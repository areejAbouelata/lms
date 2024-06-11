<?php

namespace App\Models;

use App\Observers\SliderObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();
        Slider::observe(SliderObserver::class);
    }

    public function getImageAttribute()
    {
        $image = $this->media()->exists() ? 'storage/images/slider/' . $this->media()->first()->media : 'dashboardAssets/images/cover/cover_sm.png';
        return asset($image);
    }

    public function media()
    {
        return $this->morphOne(AppMedia::class, 'app_mediaable');
    }

}
