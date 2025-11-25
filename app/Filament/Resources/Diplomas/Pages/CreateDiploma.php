<?php

namespace App\Filament\Resources\Diplomas\Pages;

use App\Filament\Resources\Diplomas\DiplomaResource;
use App\Jobs\GenerateDiplomaPdf;
use App\Livewire\Diplomas\BatchProgress;
use App\Models\Course;
use App\Models\Diploma;
use App\Models\DiplomaBatch;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CreateDiploma extends CreateRecord
{
    protected static string $resource = DiplomaResource::class;

    public function create(bool $another = false): void
    {
        $data = $this->form->getState();

        /** @var int|null $courseId */
        $courseId = $data['course_id'] ?? null;

        /** @var array<int> $teacherIds */
        $teacherIds = $data['teacher_ids'] ?? [];

        if (empty($teacherIds)) {
            Notification::make()
                ->title('Faltan docentes')
                ->body('Debes seleccionar al menos un docente para el diploma.')
                ->warning()
                ->send();

            return;
        }

        $issuedRaw = $data['issued_at'] ?? now();

        /** @var Collection<int, array> $students */
        $students = collect($data['students'] ?? []);

        if (!$courseId) {
            Notification::make()
                ->title('Faltan datos del curso')
                ->danger()
                ->send();

            return;
        }

        $issuedAt = $issuedRaw instanceof Carbon
            ? $issuedRaw
            : Carbon::parse($issuedRaw);

        $course = Course::find($courseId);
        $teachers = Teacher::whereIn('id', $teacherIds)->get();

        if (!$course || $teachers->isEmpty()) {
            Notification::make()
                ->title('No se pudo encontrar el curso o los docentes seleccionados')
                ->danger()
                ->send();

            return;
        }

        $issuedAt = $issuedRaw instanceof Carbon
            ? $issuedRaw
            : Carbon::parse($issuedRaw);

        // Solo los que tengan el toggle activado (ej: "selected" / "crear_diploma")
        $selectedStudents = $students->filter(
            fn(array $s) => !empty($s['selected'])
        );

        if ($selectedStudents->isEmpty()) {
            Notification::make()
                ->title('No se seleccionaron estudiantes')
                ->warning()
                ->send();

            return;
        }

        $course = Course::find($courseId);
        $teacher = Teacher::find($teacherIds);


        if (!$course || !$teacher) {
            Notification::make()
                ->title('No se pudo encontrar el curso o el docente seleccionados')
                ->danger()
                ->send();

            return;
        }

        // 1) Crear batch
        $batch = DiplomaBatch::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'total' => $selectedStudents->count(),
            'processed' => 0,
            'status' => 'pending',
        ]);

        $diplomaIds = [];
        $createdCount = 0;

        foreach ($selectedStudents as $row) {
            $studentId = $row['id'] ?? $row['student_id'] ?? null;
            $student = null;

            if ($studentId) {
                $student = Student::find($studentId);
            }

            if (!$student && !empty($row['rut'])) {
                $rutLimpio = preg_replace('/[^0-9kK]/', '', $row['rut']);
                $student = Student::where('rut', $rutLimpio)->first();
            }

            if (!$student) {
                continue;
            }

            $finalGrade = $row['final_grade'] ?? null;

            $diploma = Diploma::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'issued_at' => $issuedAt,
                'final_grade' => $finalGrade,
                'verification_code' => strtoupper(uniqid('DIP-')),
                'diploma_batch_id' => $batch->id,
            ]);

            $diplomaIds[] = $diploma->id;
            $createdCount++;
        }

        if (empty($diplomaIds)) {
            $batch->update([
                'total' => 0,
                'processed' => 0,
                'status' => 'failed',
            ]);

            Notification::make()
                ->title('No se pudo crear ningún diploma')
                ->body('Revisa que los estudiantes del wizard tengan un RUT válido o un ID.')
                ->danger()
                ->send();

            return;
        }

        // 2) Actualizar batch y despachar un job por diploma
        $batch->update([
            'total' => $createdCount,
            'status' => 'processing',
        ]);

        foreach ($diplomaIds as $id) {
            GenerateDiplomaPdf::dispatch($id);
        }

        // 3) Feedback + reset + volver al paso 1
        Notification::make()
            ->title('Diplomas en proceso')
            ->body("Se creó un lote de {$batch->total} diplomas. Los PDFs se están generando en segundo plano.")
            ->success()
            ->send();

        $this->form->fill();
        $this->dispatch('wizard::set-step', step: 0);

        // Para el popup de progreso (si lo tienes montado)
        $this->dispatch('diplomas-batch-started', batchId: $batch->id)
            ->to(BatchProgress::class);
    }
    
}
