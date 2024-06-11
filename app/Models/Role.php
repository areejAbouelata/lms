<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model implements TranslatableContract
{
    use Translatable;

    protected $guarded = ['id','created_at','updated_at','deleted_at'];
    public $translatedAttributes = ['name'];

    public function users()
    {
    	return $this->hasMany(User::class);
    }
    public function permissions()
    {
    	return $this->belongsToMany(Permission::class)->withTimestamps();
    }
}
