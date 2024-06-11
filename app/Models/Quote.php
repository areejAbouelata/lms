<?php

namespace App\Models;

use App\Observers\QuoteObserver;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model implements TranslatableContract
{
    use \Astrotomic\Translatable\Translatable;

    public $translatedAttributes = ['title', 'desc'];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();
        Quote::observe(QuoteObserver::class);
    }

    public function getImageAttribute()
    {
        $image = $this->media()->exists() ? 'storage/images/quote/' . $this->media()->first()->media : 'dashboardAssets/images/cover/cover_sm.png';
        return asset($image);
    }

    public function media()
    {
        return $this->morphOne(AppMedia::class, 'app_mediaable');
    }
}
