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

    /** @var array<int> */
    public array $teacherIds;

    public function __construct(int $diplomaId, array $teacherIds = [])
    {
        $this->diplomaId  = $diplomaId;
        $this->teacherIds = $teacherIds;
    }

    public function handle(): void
    {
        ini_set('memory_limit', '256M');

        $diploma = Diploma::with(['course', 'student'])->find($this->diplomaId);

        if (! $diploma || ! $diploma->course || ! $diploma->student) {
            return;
        }

        $course   = $diploma->course;
        $student  = $diploma->student;
        $issuedAt = $diploma->issued_at instanceof Carbon
            ? $diploma->issued_at
            : Carbon::parse($diploma->issued_at);

        /* ==========================
         *   DOCENTES + ORGANIZACIÓN
         * ========================== */

        // Colección de docentes seleccionados en el wizard
        $teachers = collect();

        if (! empty($this->teacherIds)) {
            $teachers = Teacher::with('organization')
                ->whereIn('id', $this->teacherIds)
                ->get();
        }

        // Fallback: si por algún motivo no llegaron teacherIds, usamos el batch
        if ($teachers->isEmpty() && $diploma->diploma_batch_id) {
            $batch = DiplomaBatch::find($diploma->diploma_batch_id);

            if ($batch && $batch->teacher_id) {
                $singleTeacher = Teacher::with('organization')->find($batch->teacher_id);
                if ($singleTeacher) {
                    $teachers = collect([$singleTeacher]);
                }
            }
        }

        // Fallback final: cualquier profe
        if ($teachers->isEmpty()) {
            $fallback = Teacher::with('organization')->first();
            if ($fallback) {
                $teachers = collect([$fallback]);
            }
        }

        // Por compatibilidad, dejamos un "teacher" principal como el primero
        $teacher      = $teachers->first();
        $organization = $teacher?->organization;

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
            'student'      => $student,
            'course'       => $course,
            'teachers'     => $teachers,
            'organization' => $organization,
            'issuedAt'     => $issuedAt,
            'finalGrade'   => $finalGrade,
            'attendance'   => $attendance,
            'qrPath'       => $qrPath,
            'diploma'      => $diploma,
        ])->setPaper('a4', 'landscape')
          ->setWarnings(false);

        $fileName = "diplomas/pdfs/diploma-{$diploma->id}.pdf";
        Storage::disk('public')->put($fileName, $pdf->output());

        $diploma->update([
            'file_path'   => $fileName,
            'final_grade' => $finalGrade,
        ]);

        /* ==========================
         *  PROGRESO DEL BATCH
         * ========================== */

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
