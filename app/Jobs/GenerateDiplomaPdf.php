<?php

namespace App\Jobs;

use App\Models\CourseStudent;
use App\Models\Diploma;
use App\Models\DiplomaBatch;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

        if (! $diploma || ! $diploma->course || ! $diploma->student) {
            return;
        }

        $course      = $diploma->course;
        $student     = $diploma->student;
        $issuedAt    = $diploma->issued_at instanceof Carbon
                        ? $diploma->issued_at
                        : Carbon::parse($diploma->issued_at);

        /* ==========================
         *   DOCENTE + ORGANIZACIÓN
         * ========================== */
        $teacher = null;
        $organization = null;

        if ($diploma->diploma_batch_id) {
            $batch = DiplomaBatch::find($diploma->diploma_batch_id);

            if ($batch && $batch->teacher_id) {
                $teacher = Teacher::with(['organization'])
                    ->find($batch->teacher_id);

                $organization = $teacher?->organization;
            }
        }

        if (!$teacher) {
            $teacher = Teacher::with('organization')->first();
            $organization = $teacher?->organization;
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
         *   QR: Código único + PNG
         * ========================== */

        if (blank($diploma->verification_code)) {
            $diploma->verification_code = strtoupper(uniqid('DIP-'));
            $diploma->save();
        }

        $verifyUrl = route('diplomas.verify', $diploma->verification_code);

        // PNG del QR
        $qrPng = QrCode::format('png')
            ->size(400)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($verifyUrl);

        $qrPath = "diplomas/qrs/qr-{$diploma->id}.png";
        Storage::disk('public')->put($qrPath, $qrPng);

        $diploma->qr_path = $qrPath;
        $diploma->save();

        /* ==========================
         *       GENERAR PDF
         * ========================== */

        $pdf = Pdf::loadView('diplomas.template', [
            'student'      => $student,
            'course'       => $course,
            'teacher'      => $teacher,
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
