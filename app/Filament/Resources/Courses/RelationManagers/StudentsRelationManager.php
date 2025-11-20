<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Illuminate\Database\Eloquent\Builder;

class StudentsRelationManager extends RelationManager
{
    // Nombre de la relación en el modelo Course
    protected static string $relationship = 'students';

    protected static ?string $title = 'Estudiantes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('apellido')
                    ->label('Apellido')
                    ->sortable()
                    ->searchable(),

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
                    ->recordSelectOptionsQuery(
                        fn (Builder $query) => $query
                            ->orderBy('apellido')
                            ->orderBy('nombre')
                    ),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Quitar'),
            ]);
    }
}
