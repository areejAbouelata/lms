<?php

namespace App\Models;

use App\Observers\CountryObserver;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model implements TranslatableContract
{
    use Translatable, SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    public $translatedAttributes = ['name', 'nationality'];

    protected static function boot()
    {
        parent::boot();
        Country::observe(CountryObserver::class);
    }

    public function getImageAttribute()
    {
        $image = $this->media()->exists() ? 'storage/images/country/' . $this->media()->first()->media : 'dashboardAssets/images/cover/cover_sm.png';
        return asset($image);
    }

    // Relations
    // ========================= Image ===================
    public function media()
    {
        return $this->morphOne(AppMedia::class, 'app_mediaable');
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

//    public function users()
//    {
//    	return $this->hasManyThrough(User::class,Profile::class);
//    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
