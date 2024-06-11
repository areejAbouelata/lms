<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model  implements TranslatableContract
{
    use \Astrotomic\Translatable\Translatable;

    public $translatedAttributes = ['title'];
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
