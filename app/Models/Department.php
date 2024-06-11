<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model implements TranslatableContract
{
    use Translatable;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    public $translatedAttributes = ['name', 'desc'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
