<?php

namespace App\Models;

use App\Observers\ActivityObserver;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use ZipArchive;

class Activity extends Model implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['name', 'desc'];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();
        Activity::observe(ActivityObserver::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class, "flow_id");
    }

    public function attachmentAttribute()
    {
        $data = [];
        foreach ($this->media()->get() as $attachment) {
            $att = $this->attachmentUrlAttribute($attachment);
            if ($att) {
                $data[] = $att;
            }
        }
        return $data;
    }

    public function media()
    {
        return $this->morphMany(AppMedia::class, 'app_mediaable');
    }

    public function attachmentUrlAttribute($attachment)
    {
        if ($attachment->option != "cover") {
            // handle zip file
            $ext = strstr($attachment->media, '.');
            if ($ext == '.zip') {
                $zip = new ZipArchive;
                $fileName = $attachment->media;
                $status = $zip->open(public_path("storage\files\activities\\$fileName"), ZipArchive::ER_COMPNOTSUPP);
                if ($status !== true) {
                    throw new Exception($status);
                } else {
                    $filename = $zip->getNameIndex(0);
                    $extension = pathinfo($filename)['extension'] ?? 'html';
                    $filePath = "storage/files/activities/unzip/";

                    $storageDestinationPath = public_path("storage/files/activities/unzip/" . time());
                    $zip->extractTo($storageDestinationPath);
//                    rename($filePath . "/$filename", $filePath . "/" . time() . "." . $extension);
                    $zip->close();
                }
                return asset($filePath . time() . '/index.html');
            }
            $image = $this->media()->exists() ? 'storage/files/activities/' . $attachment->media : 'dashboardAssets/images/cover/cover_sm.png';
            return asset($image);
        }
    }

    public function remarkAttachmentAttribute()
    {
        $attachment = $this->media()->where('media_type', '=', 'remark_file')->first();
        $url = $this->remarkAttachmentUrlAttribute($attachment);
        return $url;
    }

    public function remarkAttachmentUrlAttribute($attachment)
    {
        // if ($attachment->option != "cover") {
        $image = $this->media()->where('media_type', '=', 'remark_file')->exists() ? 'storage/files/activities/' . $attachment->media : 'dashboardAssets/images/cover/cover_sm.png';
        return asset($image);
        // }
    }

    public function getImageAttribute()
    {
        $image = $this->media()->where("option", "cover")->exists() ?
            'storage/images/activities/' . $this->media()->where("option", "cover")->first()->media :
            "dashboardAssets/activities/$this->type.jpg";
//            'dashboardAssets/images/cover/cover_sm.png';
        return asset($image);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, "activity_users", "activity_id", "user_id");
    }

    public function actiitiyUserAnswers()
    {
        return $this->hasMany(ActivityAnswerUser::class);
    }

    public function assesments()
    {
        return $this->hasMany(Assessment::class, 'activity_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function hasUsers()
    {
        return $this->activityUser()->count() > 0 ? true : false;
    }

    public function activityUser()
    {
        return $this->hasMany(ActivityUser::class);
    }

    public function getDurationLengthAttribute()
    {
        $end = null;
        switch ($this->duration_type) {
            case 'hour':
                $end = Carbon::now()->addHours($this->duration);
                break;
            case 'day':
                $end = Carbon::now()->addDays($this->duration);
                break;
            case 'month':
                $end = Carbon::now()->addMonths($this->duration);
                break;
        }
        return $end;
    }
}
