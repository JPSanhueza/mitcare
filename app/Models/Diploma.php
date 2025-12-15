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
        'diploma_batch_id',
        'issued_at',
        'final_grade',
        'file_path',
        'verification_code',
        'qr_path',
    ];
    protected static function booted(): void
    {
        static::deleted(function (Diploma $diploma) {
            // Si por alguna razón no hay curso/estudiante, no hacemos nada
            if (! $diploma->course_id || ! $diploma->student_id) {
                return;
            }

            // (Opcionalmente defensivo): si todavía existen otros diplomas
            // para este mismo course_id + student_id, NO bajar el flag.
            $otrosDiplomas = Diploma::where('course_id', $diploma->course_id)
                ->where('student_id', $diploma->student_id)
                ->exists();

            if ($otrosDiplomas) {
                return;
            }

            // Opción 1: usando la relación del curso si existe
            if ($diploma->relationLoaded('course') || $diploma->course) {
                $diploma->course
                    ->students()
                    ->updateExistingPivot($diploma->student_id, [
                        'diploma_issued' => false,
                    ]);

                return;
            }

            // Opción 2: fallback directo a la tabla pivot con el modelo CourseStudent
            \App\Models\CourseStudent::where('course_id', $diploma->course_id)
                ->where('student_id', $diploma->student_id)
                ->update([
                    'diploma_issued' => false,
                ]);
        });
    }
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

    public function batch()
    {
        return $this->belongsTo(DiplomaBatch::class, 'diploma_batch_id');
    }
}
