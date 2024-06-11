<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEvaluation extends Model
{
    protected $guarded = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }
    
    public function form()
    {
        return $this->belongsTo(EvalutionForm::class);
    }
}
