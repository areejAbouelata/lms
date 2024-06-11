<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model /*implements TranslatableContract*/
{
//    use \Astrotomic\Translatable\Translatable;

//    public $translatedAttributes = ['answer'];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function matchAnswer()
    {
        return $this->hasOne(Assessment::class, "match_answer_id");
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

}
