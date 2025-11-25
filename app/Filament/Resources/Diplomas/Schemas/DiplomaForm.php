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

    function format_rut($rut)
    {
        if (!$rut)
            return '';

        $rut = preg_replace('/[^0-9kK]/', '', $rut);

        $dv = strtoupper(substr($rut, -1));
        $num = substr($rut, 0, -1);

        if ($num === '')
            return $rut;

        $num = number_format((int) $num, 0, ',', '.');

        return $num . '-' . $dv;
    }

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
                            ->options(fn() => Course::query()
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id'))
                            ->searchable()
                            ->live(onBlur: false)
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Al cambiar el curso, limpiamos docente seleccionado
                                $set('teacher_id', null);

                                // Cuando cambia el curso, cargamos estudiantes + notas
                                if (blank($state)) {
                                    $set('students', []);
                                    return;
                                }

                                $course = Course::with('students')->find($state);

                                if (!$course) {
                                    $set('students', []);
                                    return;
                                }

                                $items = $course->students->map(function ($student) {
                                    return [
                                        'student_id' => $student->id,
                                        'name' => $student->nombre . ' ' . $student->apellido,
                                        'rut' => $student->rut,
                                        'final_grade' => $student->pivot->final_grade,
                                        'approved' => (bool) $student->pivot->approved,
                                        'attendance' => $student->pivot->attendance,
                                        // Por defecto marcamos para diploma solo a los aprobados
                                        'selected' => (bool) $student->pivot->approved,
                                    ];
                                })->toArray();

                                $set('students', $items);
                            }),


                        Select::make('teacher_id')
                            ->label('Docente')
                            ->required()
                            ->options(function (Get $get) {
                                $courseId = $get('course_id');

                                // Si aún no se ha elegido curso, no mostramos opciones
                                if (blank($courseId)) {
                                    return [];
                                }

                                // Cargamos el curso con sus profesores
                                $course = Course::with('teachers')->find($courseId);

                                if (!$course) {
                                    return [];
                                }

                                // Filtramos solo docentes activos y los ordenamos
                                return $course->teachers
                                    ->filter(fn($teacher) => $teacher->is_active)
                                    ->sortBy(fn($t) => $t->nombre . ' ' . $t->apellido)
                                    ->mapWithKeys(fn($t) => [
                                        $t->id => "{$t->nombre} {$t->apellido}",
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->hidden(fn(Get $get) => blank($get('course_id')))
                            ->helperText('El docente debe tener cargada su firma.'),

                    ]),

                /* ------------------------------
                 * PASO 2: Estudiantes
                 * ------------------------------ */
                Step::make('Estudiantes')
                    ->schema([
                        Repeater::make('students')
                            ->label('Estudiantes del curso')
                            ->visible(fn(Get $get) => filled($get('course_id')))
                            ->columns(1)

                            // 🚫 NO permitir tocar la estructura
                            ->addable(false)       // quita "Añadir a estudiantes del curso"
                            ->deletable(false)     // quita el basurero
                            ->reorderable(false)   // quita el ícono de arrastre
                            ->collapsible(false)

                            ->dehydrated(true)

                            ->schema([
                                // ÚNICO campo editable: si se crea o no el diploma
                                Toggle::make('selected')
                                    ->label('Crear diploma')
                                    ->columnSpanFull(),

                                // Vista de solo lectura con los datos del alumno
                                View::make('filament.resources.diplomas.partials.student-row')
                                    ->viewData(fn(Get $get) => [
                                        'name' => $get('name'),
                                        'rut' => $get('rut'),
                                        'final_grade' => $get('final_grade'),
                                        'approved' => $get('approved'),
                                        'attendance' => $get('attendance'),
                                    ])
                                    ->columnSpanFull(),
                            ]),
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
                            ->viewData(function (Get $get) {
                                $course = $get('course_id')
                                    ? Course::find($get('course_id'))
                                    : null;

                                $teacher = $get('teacher_id')
                                    ? Teacher::find($get('teacher_id'))
                                    : null;

                                $students = collect($get('students') ?? [])
                                    ->filter(fn($s) => $s['selected'] ?? false)
                                    ->values();

                                return [
                                    'course' => $course,
                                    'teacher' => $teacher,
                                    'students' => $students,
                                    'issued_at' => $get('issued_at'),
                                ];
                            }),
                    ]),
            ])->columnSpanFull(),
        ]);
    }
}
