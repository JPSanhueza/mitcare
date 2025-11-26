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
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Filament\Actions;
use App\Filament\Resources\Diplomas\Schemas\DiplomaForm;
use Filament\Schemas\Schema;
class CreateDiploma extends CreateRecord
{
    protected static string $resource = DiplomaResource::class;
    public function form(Schema $schema): Schema
    {
        // 👇 Aquí usas SOLO el wizard
        return DiplomaForm::configure($schema);
    }
    #[On('diplomas-batch-closed')]
    public function redirectAfterBatchClosed(): void
    {
        $this->redirect($this->getResource()::getUrl('index'));
    }
    protected function getFormActions(): array
    {
        return [];
    }
    public function create(bool $another = false): void
    {
        $data = $this->form->getState();

        /** --------------------------
         * CURSO
         * -------------------------- */
        $courseId = $data['course_id'] ?? null;

        if (! $courseId) {
            Notification::make()
                ->title('Faltan datos del curso')
                ->body('Debes seleccionar un curso antes de crear diplomas.')
                ->warning()
                ->send();

            return;
        }

        $course = Course::find($courseId);

        if (! $course) {
            Notification::make()
                ->title('Curso no encontrado')
                ->danger()
                ->send();

            return;
        }

        /** --------------------------
         * DOCENTES
         * -------------------------- */
        $teacherIds = array_filter($data['teacher_ids'] ?? []);

        if (empty($teacherIds)) {
            Notification::make()
                ->title('Faltan docentes')
                ->body('Debes seleccionar al menos un docente.')
                ->warning()
                ->send();

            return;
        }

        $teachers = Teacher::whereIn('id', $teacherIds)->get();

        if ($teachers->isEmpty()) {
            Notification::make()
                ->title('Docentes inválidos')
                ->danger()
                ->send();

            return;
        }

        // Docentes sin firma
        $teachersWithoutSignature = $teachers->filter(fn($t) => empty($t->signature));

        if ($teachersWithoutSignature->isNotEmpty()) {
            $names = $teachersWithoutSignature
                ->map(fn($t) => "{$t->nombre} {$t->apellido}")
                ->implode(', ');

            Notification::make()
                ->title('Docentes sin firma')
                ->body("Los siguientes docentes no tienen firma cargada: {$names}. Debes cargarlas antes de emitir diplomas.")
                ->danger()
                ->send();

            return;
        }

        /** --------------------------
         * FECHA DE EMISIÓN
         * -------------------------- */
        $issuedRaw = $data['issued_at'] ?? null;

        // 👈 AQUÍ se controla el “estoy en paso 1/2”
        /* if (blank($issuedRaw)) {
            Notification::make()
                ->title('Falta la fecha de emisión')
                ->body('Debes ir al paso "Confirmación" y definir la fecha de emisión antes de crear los diplomas.')
                ->warning()
                ->send();

            return;
        } */

        $issuedAt = $issuedRaw instanceof Carbon
            ? $issuedRaw
            : Carbon::parse($issuedRaw);

        /** --------------------------
         * ESTUDIANTES
         * -------------------------- */
        /** @var Collection<int, array> $students */
        $students = collect($data['students'] ?? []);

        $selectedStudents = $students->filter(
            fn(array $s) => ! empty($s['selected'])
        );

        if ($selectedStudents->isEmpty()) {
            Notification::make()
                ->title('No se seleccionaron estudiantes')
                ->body('Debes marcar al menos un estudiante con "Crear diploma".')
                ->warning()
                ->send();

            return;
        }

        /** --------------------------
         * A PARTIR DE AQUÍ: BD
         * -------------------------- */
        DB::beginTransaction();

        try {
            // 1) Crear lote
            $batch = DiplomaBatch::create([
                'course_id'  => $course->id,
                'teacher_id' => $teachers->first()->id, // el lote guarda solo 1, pero el PDF puede usar varios si luego lo amplías
                'total'      => $selectedStudents->count(),
                'processed'  => 0,
                'status'     => 'pending',
            ]);

            $diplomaIds   = [];
            $createdCount = 0;

            foreach ($selectedStudents as $row) {
                $studentId = $row['id'] ?? $row['student_id'] ?? null;
                $student   = null;

                if ($studentId) {
                    $student = Student::find($studentId);
                }

                if (! $student && ! empty($row['rut'])) {
                    $rutLimpio = preg_replace('/[^0-9kK]/', '', $row['rut']);
                    $student   = Student::where('rut', $rutLimpio)->first();
                }

                if (! $student) {
                    continue;
                }

                $finalGrade = $row['final_grade'] ?? null;

                $diploma = Diploma::create([
                    'course_id'         => $course->id,
                    'student_id'        => $student->id,
                    'issued_at'         => $issuedAt,
                    'final_grade'       => $finalGrade,
                    'verification_code' => strtoupper(uniqid('DIP-')),
                    'diploma_batch_id'  => $batch->id,
                ]);

                $diplomaIds[] = $diploma->id;
                $createdCount++;
            }

            if (empty($diplomaIds)) {
                $batch->update([
                    'total'     => 0,
                    'processed' => 0,
                    'status'    => 'failed',
                ]);

                DB::commit();

                Notification::make()
                    ->title('No se pudo crear ningún diploma')
                    ->body('Revisa que los estudiantes del wizard tengan un RUT válido o un ID.')
                    ->danger()
                    ->send();

                return;
            }

            // 2) Actualizar lote + encolar PDFs
            $batch->update([
                'total'  => $createdCount,
                'status' => 'processing',
            ]);

            foreach ($diplomaIds as $id) {
                GenerateDiplomaPdf::dispatch($id);
            }

            DB::commit();

            // 3) Notificación + popup de progreso
            Notification::make()
                ->title('Diplomas en proceso')
                ->body("Se creó un lote de {$batch->total} diplomas. Los PDFs se están generando en segundo plano.")
                ->success()
                ->send();

            $this->dispatch('diplomas-batch-started', batchId: $batch->id)
                ->to(BatchProgress::class);
        } catch (\Throwable $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un error mientras se creaban los diplomas.')
                ->danger()
                ->send();

            throw $e;
        }
    }
}
