<?php

namespace App\Jobs;

use App\Models\CourseStudent;
use App\Models\Diploma;
use App\Models\DiplomaBatch;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class GenerateDiplomaPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $diplomaId;

    public function __construct(int $diplomaId)
    {
        $this->diplomaId = $diplomaId;
    }

    public function handle(): void
    {
        // Opcional por si quieres darle más aire SOLO a este job
        ini_set('memory_limit', '256M');

        $diploma = Diploma::with(['course', 'student'])->find($this->diplomaId);

        if (! $diploma || ! $diploma->course || ! $diploma->student) {
            return;
        }

        $course  = $diploma->course;
        $student = $diploma->student;

        // Docente + organización desde el batch
        $teacher = null;
        $organization = null;

        if ($diploma->diploma_batch_id) {
            $batch = DiplomaBatch::find($diploma->diploma_batch_id);

            if ($batch && $batch->teacher_id) {
                $teacher = Teacher::with('organization')->find($batch->teacher_id);
                $organization = $teacher?->organization;
            }
        }

        if (! $teacher) {
            // fallback simple: el primer docente (ajusta si quieres)
            $teacher = Teacher::with('organization')->first();
            $organization = $teacher?->organization;
        }

        $issuedAt = $diploma->issued_at instanceof Carbon
            ? $diploma->issued_at
            : Carbon::parse($diploma->issued_at);

        // Nota y asistencia desde pivot si hace falta
        $finalGrade = $diploma->final_grade;
        $attendance = null;

        $pivot = CourseStudent::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if ($pivot) {
            $finalGrade = $finalGrade ?? $pivot->final_grade;
            $attendance = $pivot->attendance;
        }

        // Generar PDF
        $pdf = Pdf::loadView('diplomas.template', [
            'student'      => $student,
            'course'       => $course,
            'teacher'      => $teacher,
            'organization' => $organization,
            'issuedAt'     => $issuedAt,
            'finalGrade'   => $finalGrade,
            'attendance'   => $attendance,
            'diploma'      => $diploma,
        ])->setPaper('a4', 'landscape');

        $fileName = 'diplomas/diploma-' . $diploma->id . '.pdf';

        Storage::disk('public')->put($fileName, $pdf->output());

        $diploma->update([
            'file_path'   => $fileName,
            'final_grade' => $finalGrade,
        ]);
        $course->students()
            ->updateExistingPivot($student->id, ['diploma_issued' => true]);
        // Actualizar progreso del batch (si existe)
        if ($diploma->diploma_batch_id) {
            $batch = DiplomaBatch::find($diploma->diploma_batch_id);

            if ($batch) {
                $batch->increment('processed');

                if ($batch->processed >= $batch->total) {
                    $batch->update(['status' => 'done']);
                }
            }
        }

        // Por si las moscas, forzar recolección de basura
        unset($pdf);
        gc_collect_cycles();
    }
}
