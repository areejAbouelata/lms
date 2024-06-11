<?php

namespace App\Models;

use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['image'];
    protected $hidden = ['password'];
    protected $casts = ['email_verified_at' => 'datetime'];

    protected static function boot()
    {
        parent::boot();
        User::observe(UserObserver::class);
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    // public function setImageAttribute($value)
    // {
    //     if ($value && $value->isValid()) {
    //         if (isset($this->attributes['image']) && $this->attributes['image']) {
    //             if (file_exists(storage_path('app/public/images/users/' . $this->attributes['image']))) {
    //                 File::delete(storage_path('app/public/images/users/' . $this->attributes['image']));
    //             }
    //         }
    //         $image = uploadImg($value, 'user');
    //         $this->attributes['image'] = $image;
    //     }
    // }

    public function getImageAttribute()
    {
        $image = $this->media()->exists() ? 'storage/images/users/' . $this->media()->first()->media : 'images/avatar.jpg';
        return asset($image);
    }

    // Relations
    public function media()
    {
        return $this->morphOne(AppMedia::class, 'app_mediaable');
    }


    // For Notification Channel
    public function receivesBroadcastNotificationsOn()
    {
        return 'base9-notification.' . $this->id;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function jobTitile()
    {
        return $this->belongsTo(JobTitle::class, "job_title_id");
    }

    public function department()
    {
        return $this->belongsTo(Department::class, "department_id");
    }

    public function group()
    {
        return $this->belongsTo(Group::class, "group_id");
    }

    public function country()
    {
        return $this->belongsTo(Country::class, "country_id");
    }

    public function getCompletionAttribute()
    {
        $flow_user = $this->flowsUser()->latest()->first();
        if ($flow_user?->flow_id) {
            $activities = Flow::find($flow_user->flow_id)?->activities()?->pluck('id');
            $finished = $this->activitiesUser()->whereIn('activity_id', $activities)->where('status', "finished")->count();
            $all_activities = Flow::find($flow_user->flow_id)?->activities()->count();
            return $all_activities ? $finished / $all_activities * 100 : 0;
        }
        return 0;
    }

    public function flowsUser()
    {
        return $this->hasMany(FlowUser::class);
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, "activity_users", "user_id", "activity_id");
    }

    public function flows()
    {
        return $this->belongsToMany(Flow::class, "flow_users", "user_id", "flow_id");
    }

    public function activitiesUser()
    {
        return $this->hasMany(ActivityUser::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class, 'nationality_id');
    }
}
