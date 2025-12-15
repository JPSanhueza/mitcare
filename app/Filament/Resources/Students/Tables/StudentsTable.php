<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Student;
class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // Nombre completo
                TextColumn::make('nombre')
                    ->label('Nombre completo')
                    ->formatStateUsing(fn($state, $record) => trim($record->nombre . ' ' . ($record->apellido ?? '')))
                    ->searchable(['nombre', 'apellido'])
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rut')
                    ->label('RUT')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => Student::formatRut($state)),

                // Email verificado: badge tipo booleano

                // TextColumn::make('telefono')
                //     ->label('Teléfono')
                //     ->searchable()
                //     ->toggleable(),

                // TextColumn::make('direccion')
                //     ->label('Dirección')
                //     ->limit(40)
                //     ->searchable()
                //     ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
