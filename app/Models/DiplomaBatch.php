<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiplomaBatch extends Model
{
    protected $fillable = [
        'course_id', 'teacher_id', 'total', 'processed', 'status',
    ];

    public function diplomas()
    {
        return $this->hasMany(Diploma::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
