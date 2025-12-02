<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Teacher;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';
    protected static ?string $modelLabel = 'Docente';
    protected static ?string $pluralModelLabel = 'Docentes';

    protected static ?string $navigationLabel = 'Docentes';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $title = 'Docentes';

    public function table(Table $table): Table
    {
        return $table
            // 🔹 Aquí definimos cómo se "nombra" cada Teacher en el relation manager
            ->recordTitle(fn(Teacher $record): string => trim(
                $record->nombre . ' ' . ($record->apellido ?? '')
            ))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->formatStateUsing(
                        fn($state, Teacher $record) => trim($record->nombre . ' ' . ($record->apellido ?? ''))
                    )
                    ->sortable()
                    ->searchable(['nombre', 'apellido']),

                TextColumn::make('organization.nombre')
                    ->label('Organización')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('especialidad')
                    ->label('Especialidad')
                    ->limit(40)
                    ->toggleable(),

                /*                 TextColumn::make('email')
                                    ->label('Email')
                                    ->sortable()
                                    ->searchable()
                                    ->toggleable(),

                                TextColumn::make('telefono')
                                    ->label('Teléfono')
                                    ->toggleable(), */

                TextColumn::make('pivot.created_at')
                    ->label('Asignado el')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Agregar docente')
                    ->preloadRecordSelect()
                    // 🔹 columnas por las que se puede buscar en el modal
                    ->recordSelectSearchColumns(['nombre', 'apellido', 'organization']),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Quitar'),
            ]);
    }
}
