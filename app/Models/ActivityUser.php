<?php

namespace App\Models;

use App\Observers\ActivityUserObserver;
use Illuminate\Database\Eloquent\Model;

class ActivityUser extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
        protected $dates = ['finished_at'];
    protected static function boot()
    {
        parent::boot();
        ActivityUser::observe(ActivityUserObserver::class);
    }

    public function media()
    {
        return $this->morphOne(AppMedia::class, 'app_mediaable');
    }

    public function scopeUserActivities($query, $user, $activities_id)
    {
        $query->where('user_id', $user->id)->whereIn('activity_id', $activities_id);
    }

    public function scopeSuccessActivities($query, $user, $activities_id)
    {
        $query->userActivities($user, $activities_id)->where('is_correct', 1);
    }

    public function scopeSuccessTasks($query, $user, $activities_ids)
    {
        $query->userActivities($user, $activities_ids)->where('status', 'finished');
    }

    public function scopeCompletedActivities($query, $user, $activities_ids)
    {
        $query->userActivities($user, $activities_ids)->where('status', 'finished');
    }

    public function scopeAssessmentsByDuration($query, $start, $end, $user)
    {
        $query->whereDate('finished_at', ">=", $start)->where("finished_at", "<=", $end)->where("user_id", $user->id)->where("status", "finished");
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUserScore()
    {
        return ($this->activity?->total_score>0?($this->score / $this->activity?->total_score) * 100:0);
    }
    public function getAttachmentAttribute()
    {
        $image = $this->media()->exists()? asset('storage/files/activities/' . $this->media()->first()->media ): null;
        return $image;
    }
}
