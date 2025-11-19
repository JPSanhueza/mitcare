<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diploma extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'student_id',
        'issued_at',
        'final_grade',
        'file_path',
        'verification_code',
        'qr_path',
        'template_id',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'final_grade' => 'decimal:2',
    ];

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
