<?php

namespace App\Filament\Resources\Diplomas\Tables;

use App\Models\Course;
use App\Models\Diploma;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class DiplomasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.nombre')
                    ->label('Nombre completo')
                    ->formatStateUsing(
                        fn ($state, Diploma $record) => $record->student
                                ? trim(($record->student->nombre ?? '').' '.($record->student->apellido ?? ''))
                                : '-'
                    )
                    ->sortable() // Filament va a ordenar por student.nombre
                    ->searchable([
                        'student.nombre',
                        'student.apellido',
                    ]),

                // Curso, con límite de caracteres y tooltip
                TextColumn::make('course.nombre_diploma')
                    ->label('Curso')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->course?->nombre)
                    ->wrap(false)
                    ->sortable()
                    ->searchable(),

                // Profesor (desde el batch → teacher)
                TextColumn::make('profesor')
                    ->label('Profesor')
                    ->getStateUsing(function ($record) {
                        $teacher = $record->batch->teacher ?? null;

                        if (! $teacher) {
                            return '—';
                        }

                        return trim($teacher->nombre.' '.$teacher->apellido);
                    })
                    ->sortable()
                    ->toggleable(),

                // Nota final
                TextColumn::make('final_grade')
                    ->label('Nota')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                // RUT del alumno formateado, pero sobre la columna real student.rut
                TextColumn::make('student.rut')
                    ->label('RUT')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '';
                        }

                        $rut = preg_replace('/[^0-9kK]/', '', $state);
                        $dv = strtoupper(substr($rut, -1));
                        $num = substr($rut, 0, -1);

                        if ($num === '') {
                            return $rut;
                        }

                        $num = number_format((int) $num, 0, ',', '.');

                        return $num.'-'.$dv;
                    })
                    ->searchable(),

                TextColumn::make('created_at')->label('Fecha emisión')->dateTime('d-m-Y H:i')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                // Filtrar por curso
                SelectFilter::make('course_id')
                    ->label('Curso')
                    ->options(fn () => Course::orderBy('nombre')->pluck('nombre', 'id')
                    )
                    ->searchable()
                    ->preload(),

                // Filtrar por profesor
                SelectFilter::make('teacher_id')
                    ->label('Profesor')
                    ->options(fn () => Teacher::orderBy('nombre')
                        ->orderBy('apellido')
                        ->get()
                        ->mapWithKeys(fn ($t) => [
                            $t->id => "{$t->nombre} {$t->apellido}",
                        ])
                    )
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $teacherId) => $q->whereHas('batch.teacher', fn (Builder $q2) => $q2->where('id', $teacherId)
                            )
                        );
                    }),

                // Rango de fecha de emisión
                Filter::make('issued_at_range')
                    ->label('Rango de fecha')
                    ->schema([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('issued_at', '>=', $date)
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('issued_at', '<=', $date)
                            );
                    }),

                // Solo con PDF generado
                Filter::make('con_pdf')
                    ->label('Solo con PDF')
                    ->query(fn (Builder $query) => $query->whereNotNull('file_path')
                    ),

                // Solo sin PDF
                Filter::make('sin_pdf')
                    ->label('Sin PDF')
                    ->query(fn (Builder $query) => $query->whereNull('file_path')
                    ),
            ])

            ->defaultSort('issued_at', 'desc')
            ->recordActions([
                EditAction::make(),

                Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn ($record) => $record->file_path
                        ? Storage::disk('public')->url($record->file_path)
                        : null
                    )
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->file_path)),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
