<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Jobs\GenerateDiplomaPdf;
use App\Models\Course;
use App\Models\Diploma;
use App\Models\DiplomaBatch;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DiplomasRelationManager extends RelationManager
{
    protected static string $relationship = 'diplomas';

    protected static ?string $title = 'Certificados';

    protected static ?string $modelLabel = 'Certificado';

    protected static ?string $pluralModelLabel = 'Certificados';

    public function table(Table $table): Table
    {
        return $table
            ->poll('3s')
            ->columns([
                TextColumn::make('course.nombre_diploma')
                    ->label('Curso')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('issued_at')
                    ->label('Fecha emisión')
                    ->date('d-m-Y')
                    ->sortable(),

                TextColumn::make('final_grade')
                    ->label('Nota final')
                    ->numeric(2),

                TextColumn::make('verification_code')
                    ->label('Código verificación')
                    ->copyable(),
            ])
            ->headerActions([
                Action::make('createCertificate')
                    ->label('Agregar certificado')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Emitir certificado')
                    ->form([
                        // 🔹 Curso: solo cursos donde está inscrito el estudiante
                        Forms\Components\Select::make('course_id')
                            ->label('Curso')
                            ->options(function () {
                                /** @var Student $student */
                                $student = $this->getOwnerRecord();

                                return $student->courses()
                                    ->where(function ($q) {
                                        // Solo cursos donde el pivote indica que NO se ha emitido diploma
                                        $q->where('course_student.diploma_issued', false)
                                            ->orWhereNull('course_student.diploma_issued');
                                    })
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'courses.id');
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                /** @var Student $student */
                                $student = $this->getOwnerRecord();

                                if (! $state) {
                                    $set('final_grade', null);
                                    $set('attendance', null);

                                    return;
                                }

                                $course = $student->courses()
                                    ->where('courses.id', $state)
                                    ->first();

                                $pivot = $course?->pivot;

                                $set('final_grade', $pivot?->final_grade);
                                $set('attendance', $pivot?->attendance);
                            })
                            ->required(),

                        // 🔹 Docentes: solo los del curso seleccionado
                        Forms\Components\Select::make('teacher_ids')
                            ->label('Docentes')
                            ->options(function (Get $get) {
                                $courseId = $get('course_id');

                                if (! $courseId) {
                                    return [];
                                }

                                return Teacher::query()
                                    ->whereHas('courses', fn ($q) => $q->where('courses.id', $courseId))
                                    ->orderBy('apellido')
                                    ->orderBy('nombre')
                                    ->get()
                                    ->mapWithKeys(fn (Teacher $t) => [
                                        $t->id => trim($t->nombre.' '.($t->apellido ?? '')),
                                    ]);
                            })
                            ->multiple()
                            ->searchable()
                            ->required()
                            ->disabled(fn (Get $get) => ! $get('course_id')),

                        Forms\Components\DatePicker::make('issued_at')
                            ->label('Fecha de emisión')
                            ->default(now())
                            ->required(),

                        // 🔹 Nota final (solo lectura, pero visible)
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('final_grade')
                                    ->label('Nota final')
                                    ->numeric()
                                    ->step(0.1)
                                    ->minValue(1)
                                    ->maxValue(7)
                                    ->disabled(),

                                Forms\Components\TextInput::make('attendance')
                                    ->label('Asistencia (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->disabled(),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        /** @var Student $student */
                        $student = $this->getOwnerRecord();

                        // 🔽 Aquí va TODO lo que ya tenías para crear batch + diploma
                        // (lo que pegaste en tu versión que funcionaba)
                        // ⬇⬇⬇
                        $course = Course::find($data['course_id'] ?? null);

                        if (! $course) {
                            Notification::make()
                                ->title('Curso no encontrado')
                                ->danger()
                                ->send();

                            return;
                        }

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

                        $teachersWithoutSignature = $teachers->filter(fn ($t) => empty($t->signature));

                        if ($teachersWithoutSignature->isNotEmpty()) {
                            $names = $teachersWithoutSignature
                                ->map(fn ($t) => "{$t->nombre} {$t->apellido}")
                                ->implode(', ');

                            Notification::make()
                                ->title('Docentes sin firma')
                                ->body("Los siguientes docentes no tienen firma cargada: {$names}. Debes cargarlas antes de emitir diplomas.")
                                ->danger()
                                ->send();

                            return;
                        }

                        $issuedRaw = $data['issued_at'] ?? now();

                        $issuedAt = $issuedRaw instanceof Carbon
                            ? $issuedRaw
                            : Carbon::parse($issuedRaw);

                        DB::beginTransaction();

                        try {
                            $batch = DiplomaBatch::create([
                                'course_id' => $course->id,
                                'teacher_id' => $teachers->first()->id,
                                'teacher_ids' => $teacherIds,
                                'total' => 1,
                                'processed' => 0,
                                'status' => 'pending',
                            ]);

                            $diploma = Diploma::create([
                                'course_id' => $course->id,
                                'student_id' => $student->id,
                                'issued_at' => $issuedAt,
                                'final_grade' => $data['final_grade'] ?? null,
                                'verification_code' => strtoupper(uniqid('DIP-')),
                                'diploma_batch_id' => $batch->id,
                            ]);

                            $batch->update([
                                'status' => 'processing',
                            ]);

                            GenerateDiplomaPdf::dispatch($diploma->id, $teacherIds);

                            DB::commit();

                            Notification::make()
                                ->title('Certificado en proceso')
                                ->body('Se emitió el certificado y se está generando el PDF en segundo plano.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Error inesperado')
                                ->body('Ocurrió un error al emitir el certificado.')
                                ->danger()
                                ->send();

                            throw $e;
                        }
                    }),
            ])
            ->recordActions([
                Action::make('downloadDiploma')
                    ->label(fn (Diploma $record) => $record->file_path ? 'Descargar' : 'PDF en proceso')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(function (Diploma $record) {
                        if (! $record->file_path) {
                            return null;
                        }

                        return Storage::disk('public')->url($record->file_path);
                    })
                    ->openUrlInNewTab()
                    ->disabled(fn (Diploma $record) => blank($record->file_path))
                    ->tooltip(
                        fn (Diploma $record) => $record->file_path
                        ? 'Descargar certificado en PDF'
                        : 'El PDF aún se está generando, recarga en unos segundos.'
                    ),

                DeleteAction::make()
                    ->label('Borrar')
                    ->icon('heroicon-o-trash'),
            ]);
    }
}
