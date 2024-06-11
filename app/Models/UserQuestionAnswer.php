<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuestionAnswer extends Model
{
    protected $guarded = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

}
