<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrochureRequest extends Model
{
    protected $fillable = [
        'course_id',
        'full_name',
        'email',
        'phone',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
