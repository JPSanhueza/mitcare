<?php

namespace App\Filament\Resources\Organizations\RelationManagers;

use App\Models\Teacher;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers'; // relación en Organization
    protected static ?string $title = 'Docentes';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn (Teacher $record) => "{$record->nombre} {$record->apellido}")
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('apellido')
                    ->label('Apellido')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Agregar docente')
                    ->preloadRecordSelect()
                    // solo profes sin organización, o saca el whereNull si quieres poder reasignar:
                    ->recordSelectOptionsQuery(
                        fn (Builder $query) => $query
                            ->whereNull('organization_id')
                            ->orderBy('apellido')
                            ->orderBy('nombre')
                    )
                    ->recordSelectSearchColumns(['nombre', 'apellido', 'email']),
            ])
            ->recordActions([
                DissociateAction::make()
                    ->label('Quitar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Quitar seleccionados'),
                ]),
            ]);
    }
}
