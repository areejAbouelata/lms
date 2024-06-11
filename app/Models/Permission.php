<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Permission extends Model implements TranslatableContract
{
    use Translatable;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    public $translatedAttributes = ['title'];

    public function roles()
    {

        return $this->belongsToMany(Role::class)->withTimestamps();
    }
    public function permissionCategory()
    {
        return $this->belongsTo(PermissionCategory::class);
    }
}
