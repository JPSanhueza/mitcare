<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Student;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsRelationManager extends RelationManager
{
    // Nombre de la relación en el modelo Course
    protected static string $relationship = 'students';
    protected static string $navigationLabel = 'Estudiante';
    protected static ?string $title = 'Estudiantes';

    public function table(Table $table): Table
    {
        return $table
            // 🔹 Cómo se "nombra" cada estudiante (lo que verás en el select del Attach)
            ->recordTitle(fn(Student $record): string => trim(
                $record->nombre . ' ' . ($record->apellido ?? '')
            ))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre completo')
                    ->formatStateUsing(
                        fn($state, Student $record) => trim($record->nombre . ' ' . ($record->apellido ?? ''))
                    )
                    ->sortable()
                    ->searchable(['nombre', 'apellido']),

                TextColumn::make('rut')
                    ->label('RUT'),

                TextColumn::make('pivot.final_grade')
                    ->label('Nota final'),

                TextColumn::make('pivot.approved')
                    ->label('Aprobado')
                    ->formatStateUsing(fn($state) => $state ? 'Sí' : 'No'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Agregar estudiante')
                    ->preloadRecordSelect()
                    // 🔹 Ordenar el listado del select
                    ->recordSelectOptionsQuery(
                        fn(Builder $query) => $query
                            ->orderBy('apellido')
                            ->orderBy('nombre')
                    )
                    // 🔹 Buscar por nombre / apellido / RUT en el modal
                    ->recordSelectSearchColumns(['nombre', 'apellido', 'rut']),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Quitar'),
            ]);
    }
}
