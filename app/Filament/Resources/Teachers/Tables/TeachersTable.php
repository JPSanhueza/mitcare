<?php

namespace App\Filament\Resources\Teachers\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ToggleColumn;

class TeachersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')->label('Foto')->circular(),
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('apellido')->label('Apellido')->searchable()->sortable(),
                TextColumn::make('organization')->label('Organización')->searchable()->sortable(),

                TextColumn::make('order')
                    ->label('Orden')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('email')->label('Correo')->searchable()->toggleable(),
                // TextColumn::make('telefono')->label('Teléfono')->toggleable(),
                ToggleColumn::make('is_active')->label('estado'),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Activos')->boolean(),
            ])
            ->reorderable('order')
            ->defaultSort('order')
            ->searchable()
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
