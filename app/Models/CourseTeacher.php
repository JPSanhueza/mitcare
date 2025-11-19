<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseTeacher extends Pivot
{
    use HasFactory;

    protected $table = 'course_teacher';

    protected $fillable = [
        'course_id',
        'teacher_id',
        'role',
    ];

    public $timestamps = true;

    /* ----------------- RELACIONES ----------------- */

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
