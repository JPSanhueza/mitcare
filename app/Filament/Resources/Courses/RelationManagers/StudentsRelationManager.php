<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Student;
use App\Rules\ValidRut;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsRelationManager extends RelationManager
{
    // Nombre de la relación en el modelo Course
    protected static string $relationship = 'students';

    protected static string $navigationLabel = 'Estudiante';

    protected static ?string $modelLabel = 'Estudiante';

    protected static ?string $pluralModelLabel = 'Estudiantes';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?string $title = 'Estudiantes';

    public function table(Table $table): Table
    {
        return $table
            // 🔹 Cómo se "nombra" cada estudiante (lo que verás en el select del Attach)
            ->recordTitle(fn (Student $record): string => trim(
                $record->nombre.' '.($record->apellido ?? '')
            ))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre completo')
                    ->formatStateUsing(
                        fn ($state, Student $record) => trim($record->nombre.' '.($record->apellido ?? ''))
                    )
                    ->sortable()
                    ->searchable(['nombre', 'apellido']),

                TextColumn::make('rut')
                    ->label('RUT'),

                TextColumn::make('pivot.final_grade')
                    ->label('Nota final'),

                TextColumn::make('pivot.approved')
                    ->label('Aprobado')
                    ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Agregar estudiante')
                    ->preloadRecordSelect()
                    // 🔹 Ordenar el listado del select
                    ->multiple()
                    ->recordSelectOptionsQuery(
                        fn (Builder $query) => $query
                            ->orderBy('apellido')
                            ->orderBy('nombre')
                    )
                    // 🔹 Buscar por nombre / apellido / RUT en el modal
                    ->recordSelectSearchColumns(['nombre', 'apellido', 'rut']),
            ])
            ->recordActions([
                // 👇 Acción para editar nota (y otros campos del pivote si quieres)
                Action::make('editEnrollment')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([

                        TextInput::make('rut')
                            ->label('RUT')
                            ->nullable()
                            ->placeholder('12.345.678-5')
                            ->maxLength(20)
                            ->rules([new ValidRut])
                            ->rule(function (?Student $record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (blank($value)) {
                                        return;
                                    }

                                    $normalized = Student::normalizeRut((string) $value);

                                    $query = Student::query()->where('rut', $normalized);

                                    if ($record) {
                                        $query->whereKeyNot($record->getKey());
                                    }

                                    if ($query->exists()) {
                                        $fail('El RUT ingresado ya está registrado.');
                                    }
                                };
                            })
                            ->afterStateHydrated(function (TextInput $component, $state) {
                                if ($state) {
                                    $component->state(Student::formatRut($state));
                                }
                            })
                            ->dehydrateStateUsing(function ($state) {
                                if (blank($state)) {
                                    return null;
                                }

                                return Student::normalizeRut($state);
                            }),

                        Forms\Components\TextInput::make('final_grade')
                            ->label('Nota final')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(1)      // típico 1–7, ajusta si usas otra escala
                            ->maxValue(7)
                            ->required(),

                        Forms\Components\TextInput::make('attendance')
                            ->label('Asistencia (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\Toggle::make('approved')
                            ->label('Aprobado'),
                    ])
                    // Cargar valores actuales del pivote en el formulario
                    ->fillForm(function (Student $record): array {
                        /** @var \App\Models\Course $course */
                        $course = $this->getOwnerRecord();

                        $pivot = $course->students()
                            ->where('students.id', $record->id)
                            ->first()
                            ?->pivot;

                        return [
                            'rut' => $record->rut,
                            'final_grade' => $pivot?->final_grade,
                            'approved' => $pivot?->approved,
                            'attendance' => $pivot?->attendance,
                        ];
                    })
                    // Guardar cambios en la tabla pivote
                    ->action(function (Student $record, array $data): void {
                        /** @var \App\Models\Course $course */
                        $course = $this->getOwnerRecord();

                        if (array_key_exists('rut', $data)) {
                            $record->rut = $data['rut']; // puede ser null o string normalizado
                            $record->save();             // dispara Student::updating
                        }

                        $course->students()->updateExistingPivot($record->id, [
                            'final_grade' => $data['final_grade'],
                            'approved' => $data['approved'] ?? false,
                            'attendance' => $data['attendance'] ?? null,
                        ]);
                    }),

                DetachAction::make()
                    ->label('Quitar'),
            ]);
    }
}
