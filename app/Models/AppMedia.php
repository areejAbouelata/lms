<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppMedia extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function app_mediaable()
    {
    	return $this->morphTo();
    }
}
