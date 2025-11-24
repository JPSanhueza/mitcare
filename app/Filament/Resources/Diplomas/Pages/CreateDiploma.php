<?php

namespace App\Filament\Resources\Diplomas\Pages;

use App\Filament\Resources\Diplomas\DiplomaResource;
use App\Models\Course;
use App\Models\Diploma;
use App\Models\Student;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CreateDiploma extends CreateRecord
{
    protected static string $resource = DiplomaResource::class;

    public function create(bool $another = false): void
    {
        $data = $this->form->getState();

        $courseId = $data['course_id'] ?? null;
        $teacherId = $data['teacher_id'] ?? null;
        $issuedRaw = $data['issued_at'] ?? now();
        $students = collect($data['students'] ?? []);

        if (! $courseId || ! $teacherId) {
            Notification::make()
                ->title('Faltan datos del curso o docente')
                ->danger()
                ->send();

            return;
        }

        // Parseamos fecha
        $issuedAt = $issuedRaw instanceof \Carbon\Carbon
            ? $issuedRaw
            : Carbon::parse($issuedRaw);

        // Filtrar alumnos marcados
        $selectedStudents = $students->filter(
            fn ($s) => ! empty($s['selected'])
        );

        if ($selectedStudents->isEmpty()) {
            Notification::make()
                ->title('No se seleccionaron estudiantes')
                ->warning()
                ->send();

            return;
        }

        // Cargar modelos base
        $course = Course::findOrFail($courseId);
        $teacher = Teacher::with('organization')->findOrFail($teacherId);
        $organization = $teacher->organization; // por la nueva FK organization_id

        $createdCount = 0;

        foreach ($selectedStudents as $row) {
            $studentId = $row['student_id'];
            $finalGrade = $row['final_grade'] ?? null;
            $attendance = $row['attendance'] ?? 0;

            $student = Student::find($studentId);

            if (! $student) {
                continue;
            }

            // 1) Crear registro Diploma (aún sin file_path)
            $diploma = Diploma::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'issued_at' => $issuedAt,
                'final_grade' => $finalGrade,
                'verification_code' => strtoupper(uniqid('DIP-')),
                // file_path y qr_path se completan luego
            ]);

            // 2) Generar PDF desde la vista
            $pdf = Pdf::loadView('diplomas.template', [
                'student' => $student,
                'course' => $course,
                'teacher' => $teacher,
                'organization' => $organization,
                'issuedAt' => $issuedAt,
                'finalGrade' => $finalGrade,
                'attendance' => $attendance,
                'diploma' => $diploma,
            ])->setPaper('a4', 'landscape'); // ajusta orientación si quieres

            // 3) Guardar en storage
            $fileName = 'diplomas/diploma-'.$diploma->id.'.pdf';

            Storage::disk('public')->put($fileName, $pdf->output());

            // 4) Actualizar path en el diploma
            $diploma->update([
                'file_path' => $fileName,
            ]);

            $createdCount++;
        }

        Notification::make()
            ->title('Diplomas generados')
            ->body("Se generaron {$createdCount} diplomas en PDF.")
            ->success()
            ->send();

        // Limpiar wizard
        $this->form->fill();
        $this->dispatch('wizard::set-step', step: 0);
    }
}
