<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NationalityTranslation extends Model
{
    public $timestamps = false;
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
