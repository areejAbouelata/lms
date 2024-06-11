<?php

namespace App\Models;

use App\Observers\FlowObserver;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flow extends Model implements TranslatableContract
{
    use Translatable;

    protected static function boot()
    {
        parent::boot();
        Flow::observe(FlowObserver::class);
    }

    protected $guarded = ['id', 'created_at', 'updated_at'];
    public $translatedAttributes = ['name', 'desc'];

    public function getImageAttribute()
    {
        $image = $this->media()->exists() ? 'storage/images/flow/' . $this->media()->first()->media : 'dashboardAssets/images/cover/cover_sm.png';
        return asset($image);
    }

    public function media()
    {
        return $this->morphOne(AppMedia::class, 'app_mediaable');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, "flow_users", "flow_id", "user_id");
    }

    public function scopeDistinctUsers()
    {
        return $this->users()->distinct();
    }

    public function scopeActiveFlow($query)
    {
        return $query->where("is_active", 1);
    }

    public function flowUsers()
    {
        return $this->hasMany(FlowUser::class);
    }

    public function getCompletionAttribute()
    {
        $all = $this->flowUsers()->count();
        $finished = $this->flowUsers()->where('status', "finished")->count();
        return $all ? $finished / $all * 100 : 0;
    }

    public function allActivityUsers()
    {
        return $this->hasManyThrough(ActivityUser::class , Activity::class) ;
    }

}
