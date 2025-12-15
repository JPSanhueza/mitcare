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
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Actions\Action;

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
                    ->beforeValidation(function (Get $get) {

                        // ⚠️ Validación de Filament --> interceptamos el caso requerido vacío
                        if ($get('course_id') === null || $get('course_id') === '') {
                            Notification::make()
                                ->title('Curso requerido')
                                ->body('Debes seleccionar un curso antes de continuar.')
                                ->danger()
                                ->send();

                            throw new \Filament\Support\Exceptions\Halt();
                        }

                        // --- TU VALIDACIÓN DE FIRMAS ---
                        $teacherIds = $get('teacher_ids') ?? [];
                        $teachers = \App\Models\Teacher::whereIn('id', $teacherIds)->get();

                        $missing = $teachers->filter(fn($t) => empty($t->signature));

                        if ($missing->isNotEmpty()) {
                            $names = $missing->map(fn($t) => "{$t->nombre} {$t->apellido}")->implode(', ');

                            Notification::make()
                                ->title('Docentes sin firma')
                                ->body("Los siguientes docentes no tienen firma cargada: $names.")
                                ->danger()
                                ->send();

                            throw new \Filament\Support\Exceptions\Halt();
                        }
                    })
                    ->schema([

                        Select::make('course_id')
                            ->label('Curso')
                            ->required()
                            ->options(fn() => Course::query()
                                ->orderBy('nombre_diploma')
                                ->pluck('nombre_diploma', 'id'))
                            ->searchable()
                            ->live(onBlur: false)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $set('teacher_ids', []);

                                // Cuando cambia el curso, cargamos estudiantes + notas
                                if (blank($state)) {
                                    $set('students', []);
                                    return;
                                }

                                // ⬇️ Traemos el curso con sus estudiantes
                                $course = Course::with('students')->find($state);

                                if (! $course) {
                                    $set('students', []);
                                    return;
                                }

                                // ⬇️ Leemos el toggle
                                $includeIssued = (bool) $get('include_issued');

                                // Partimos de todos los estudiantes
                                $students = $course->students;

                                // Si NO queremos incluir emitidos, filtramos
                                if (! $includeIssued) {
                                    $students = $students->filter(function ($student) {
                                        return ! (bool) ($student->pivot->diploma_issued ?? false);
                                    });
                                }

                                $items = $students
                                    ->map(function ($student) {
                                        return [
                                            'student_id'  => $student->id,
                                            'name'        => $student->nombre . ' ' . $student->apellido,
                                            'rut'         => $student->rut,
                                            'final_grade' => $student->pivot->final_grade,
                                            'approved'    => (bool) $student->pivot->approved,
                                            'attendance'  => $student->pivot->attendance,
                                            // Por defecto marcamos para diploma solo a los aprobados
                                            'selected'    => (bool) $student->pivot->approved,
                                        ];
                                    })
                                    ->values()
                                    ->toArray();

                                $set('students', $items);

                                if (! $includeIssued && empty($items)) {
                                    Notification::make()
                                        ->title('Sin estudiantes disponibles')
                                        ->body('Todos los estudiantes de este curso ya tienen diploma emitido.')
                                        ->info()
                                        ->send();
                                }
                            }),



                        Select::make('teacher_ids')
                            ->label('Docentes')
                            ->required()        // al menos 1 docente
                            ->multiple()        // 👈 clave: selección múltiple
                            ->rules(['required', 'array', 'min:1'])
                            ->validationMessages([
                                'required' => 'Debes seleccionar al menos un docente.',
                                'min'      => 'Debes seleccionar al menos un docente.',
                            ])
                            ->options(function (Get $get) {
                                $courseId = $get('course_id');

                                if (blank($courseId)) {
                                    return [];
                                }

                                $course = Course::with('teachers')->find($courseId);

                                if (!$course) {
                                    return [];
                                }

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
                            ->helperText('Solo se muestran docentes activos asociados a este curso. Asegúrate de que tengan su firma cargada.'),


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
                    // aquí puedes tener tu beforeValidation() si ya lo pusiste
                    ->schema([
                        DatePicker::make('issued_at')
                            ->label('Fecha de emisión')
                            ->default(now())
                            ->disabled()
                            ->required(),

                        View::make('filament.resources.diplomas.partials.summary')
                            ->viewData(function (Get $get) {
                                $course = $get('course_id')
                                    ? Course::find($get('course_id'))
                                    : null;

                                $teacherIds = array_filter($get('teacher_ids') ?? []);
                                $teachers = collect();

                                if (! empty($teacherIds)) {
                                    $teachers = Teacher::whereIn('id', $teacherIds)
                                        ->orderBy('nombre')
                                        ->orderBy('apellido')
                                        ->get();
                                }

                                $students = collect($get('students') ?? [])
                                    ->filter(fn($s) => $s['selected'] ?? false)
                                    ->values();

                                return [
                                    'course'    => $course,
                                    'teachers'  => $teachers,
                                    'students'  => $students,
                                    'issued_at' => $get('issued_at'),
                                ];
                            }),
                    ]),
            ])
                ->submitAction(
                    new HtmlString(
                        Blade::render(<<<'BLADE'
            <x-filament::button
                type="submit"
                size="lg"
                color="primary"
            >
                Crear diplomas
            </x-filament::button>
        BLADE)
                    )
                )
                ->columnSpanFull()

                ->columnSpanFull(),
        ]);
    }
}
