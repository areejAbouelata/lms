<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteTranslation extends Model
{
    public $timestamps = false;
    protected $guarded = ['id', 'created_at', 'updated_at'];

}
