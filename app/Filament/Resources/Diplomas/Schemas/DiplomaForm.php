<?php

namespace App\Filament\Resources\Diplomas\Schemas;

use App\Models\Course;
use App\Models\Teacher;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class DiplomaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                /* ------------------------------
                 * PASO 1: Curso y docente
                 * ------------------------------ */
                Step::make('Curso y docente')
                    ->schema([
                        Select::make('course_id')
                            ->label('Curso')
                            ->required()
                            ->options(fn () => Course::query()
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id'))
                            ->searchable()
                            ->live(onBlur: false)
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Cuando cambia el curso, cargamos estudiantes + notas
                                if (blank($state)) {
                                    $set('students', []);
                                    return;
                                }

                                $course = Course::with('students')->find($state);

                                if (! $course) {
                                    $set('students', []);
                                    return;
                                }

                                $items = $course->students->map(function ($student) {
                                    return [
                                        'student_id'  => $student->id,
                                        'name'        => $student->nombre.' '.$student->apellido,
                                        'rut'         => $student->rut,
                                        'final_grade' => $student->pivot->final_grade,
                                        'approved'    => (bool) $student->pivot->approved,
                                        'attendance'  => $student->pivot->attendance,
                                        // Por defecto marcamos para diploma solo a los aprobados
                                        'selected'    => (bool) $student->pivot->approved,
                                    ];
                                })->toArray();

                                $set('students', $items);
                            }),

                        Select::make('teacher_id')
                            ->label('Docente')
                            ->required()
                            ->options(function () {
                                return Teacher::query()
                                    ->where('is_active', true)
                                    ->orderBy('nombre')
                                    ->orderBy('apellido')
                                    ->get()
                                    ->mapWithKeys(fn ($t) => [
                                        $t->id => "{$t->nombre} {$t->apellido}",
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->helperText('El docente debe tener cargada su firma.'),
                    ]),

                /* ------------------------------
                 * PASO 2: Estudiantes
                 * ------------------------------ */
                Step::make('Estudiantes')
                    ->schema([
                        Repeater::make('students')
                            ->label('Estudiantes del curso')
                            ->visible(fn (Get $get) => filled($get('course_id')))
                            ->columns(6)
                            ->schema([
                                Hidden::make('student_id'),

                                Toggle::make('selected')
                                    ->label('Crear diploma')
                                    ->inline(false),

                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(2),

                                TextInput::make('rut')
                                    ->label('RUT')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('final_grade')
                                    ->label('Nota final')
                                    ->disabled()
                                    ->dehydrated(false),

                                Toggle::make('approved')
                                    ->label('Aprobado')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('attendance')
                                    ->label('Asistencia')
                                    ->suffix('%')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            // Queremos el array completo en el estado del form
                            ->dehydrated(true),
                    ]),

                /* ------------------------------
                 * PASO 3: Fecha + resumen
                 * ------------------------------ */
                Step::make('Confirmación')
                    ->schema([
                        DatePicker::make('issued_at')
                            ->label('Fecha de emisión')
                            ->default(now())
                            ->required(),

                        View::make('filament.resources.diplomas.partials.summary')
                            // ->label('Resumen')
                            ->viewData(function (Get $get) {
                                $course = $get('course_id')
                                    ? Course::find($get('course_id'))
                                    : null;

                                $teacher = $get('teacher_id')
                                    ? Teacher::find($get('teacher_id'))
                                    : null;

                                $students = collect($get('students') ?? [])
                                    ->filter(fn ($s) => $s['selected'] ?? false)
                                    ->values();

                                return [
                                    'course'    => $course,
                                    'teacher'   => $teacher,
                                    'students'  => $students,
                                    'issued_at' => $get('issued_at'),
                                ];
                            }),
                    ]),
            ])->columnSpanFull(),
        ]);
    }
}
