<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiplomaBatch extends Model
{
    protected $fillable = [
        'course_id',
        'teacher_id',   // “principal” (primero)
        'teacher_ids', 
        'total',
        'processed',
        'status',
    ];

    protected $casts = [
        'teacher_ids' => 'array',
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
