<?php

namespace App\Models;

use App\Observers\GroupObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Group extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    public $translatedAttributes = ['name', 'desc'];

    protected static function boot()
    {
        parent::boot();
        Group::observe(GroupObserver::class);
    }

    public function getImageAttribute()
    {
        $image = $this->media()->exists() ? 'storage/images/groups/' . $this->media()->first()->media : 'dashboardAssets/images/cover/cover_sm.png';
        return asset($image);
    }

    public function media()
    {
        return $this->morphOne(AppMedia::class, 'app_mediaable');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, "group_id");
    }

    public function getEnrollmentAttribute()
    {
        $users = $this->users()->pluck('id');
        return FlowUser::whereIn('user_id', $users)->count();
    }

    public function getCompletionAttribute()
    {
        $users = $this->users()->pluck('id');
        return FlowUser::whereIn('user_id', $users)->count() ? FlowUser::whereIn('user_id', $users)->where("status", "finished")->count() / FlowUser::whereIn('user_id', $users)->count() * 100 : 0;
    }
}
