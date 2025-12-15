<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseStudent extends Pivot
{
    use HasFactory;

    protected $table = 'course_student';

    protected $fillable = [
        'course_id',
        'student_id',
        'enrolled_at',
        'final_grade',
        'approved',
        'attendance',
        'diploma_issued',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'final_grade' => 'decimal:2',
        'approved' => 'boolean',
        'attendance' => 'integer',
        'diploma_issued' => 'boolean',
    ];

    public $timestamps = true;

    /* ----------------- RELACIONES ----------------- */

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
