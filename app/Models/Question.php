<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
