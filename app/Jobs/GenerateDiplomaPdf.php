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
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        ini_set('memory_limit', '256M');

        $diploma = Diploma::with(['course', 'student'])->find($this->diplomaId);

        if (!$diploma || !$diploma->course || !$diploma->student) {
            return;
        }

        $course = $diploma->course;
        $student = $diploma->student;
        $issuedAt = $diploma->issued_at instanceof Carbon
            ? $diploma->issued_at
            : Carbon::parse($diploma->issued_at);

        /* ==========================
         *   DOCENTES + ORGANIZACIÓN
         * ========================== */

        // Colección de docentes seleccionados en el wizard
        $teachers = collect();
        $batch = $diploma->batch ?? null;

        if ($batch) {
            // 1) Intentar usar teacher_ids (nuevo)
            if (!empty($batch->teacher_ids)) {
                $ids = is_array($batch->teacher_ids)
                    ? $batch->teacher_ids
                    : json_decode($batch->teacher_ids, true);

                $ids = array_filter((array) $ids);

                if (!empty($ids)) {
                    // Ya no hay relación organization, solo usamos organization_name
                    $teachers = Teacher::query()
                        ->whereIn('id', $ids)
                        ->get();
                }
            }

            // 2) Compatibilidad hacia atrás: si no hay teacher_ids, usar teacher_id único
            if ($teachers->isEmpty() && $batch->teacher_id) {
                $singleTeacher = Teacher::find($batch->teacher_id);

                if ($singleTeacher) {
                    $teachers = collect([$singleTeacher]);
                }
            }
        }

        // Fallback final: cualquier profe
        if ($teachers->isEmpty()) {
            $fallback = Teacher::first();
            if ($fallback) {
                $teachers = collect([$fallback]);
            }
        }

        // Por compatibilidad, dejamos un "teacher" principal como el primero
        $teacher = $teachers->first();

        // Si hay varios docentes, unimos sus organizaciones únicas con " / "
        $organization = $teachers
            ->pluck('organization_name')
            ->filter()
            ->unique()
            ->implode(' / ');

        // Si por alguna razón quedó vacío, al menos usa la del primero
        if (blank($organization) && $teacher) {
            $organization = $teacher->organization_name;
        }

        /* ==========================
         *   NOTA y ASISTENCIA
         * ========================== */

        $finalGrade = $diploma->final_grade;
        $attendance = null;

        $pivot = CourseStudent::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if ($pivot) {
            $finalGrade = $finalGrade ?? $pivot->final_grade;
            $attendance = $pivot->attendance;
        }

        /* ==========================
         *   QR: Código único + SVG
         * ========================== */

        if (blank($diploma->verification_code)) {
            $diploma->verification_code = Str::uuid()->toString();
            $diploma->save();
        }

        $verifyUrl = route('diplomas.verify', $diploma->verification_code);

        $qrSvg = QrCode::format('svg')
            ->size(400)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($verifyUrl);

        $qrPath = "diplomas/qrs/qr-{$diploma->id}.svg";
        Storage::disk('public')->put($qrPath, $qrSvg);

        $diploma->qr_path = $qrPath;
        $diploma->save();

        /* ==========================
         *       GENERAR PDF
         * ========================== */

        $pdf = Pdf::loadView('diplomas.template', [
            'student' => $student,
            'course' => $course,
            'teachers' => $teachers,
            'organization' => $organization, 
            'issuedAt' => $issuedAt,
            'finalGrade' => $finalGrade,
            'attendance' => $attendance,
            'qrPath' => $qrPath,
            'diploma' => $diploma,
        ])->setPaper('a4', 'landscape')
            ->setWarnings(false);

        $fileName = "diplomas/pdfs/certificado-curso-{$diploma->course->id}-rut-{$diploma->student->rut}.pdf";
        Storage::disk('public')->put($fileName, $pdf->output());

        $diploma->update([
            'file_path' => $fileName,
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

        unset($pdf);
        gc_collect_cycles();
    }
}
