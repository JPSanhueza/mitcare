<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Cursos del pedido';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('course_name')
            ->columns([
                TextColumn::make('course_name')->label('Curso')->searchable(),
                TextColumn::make('unit_price')->label('Precio unit.')
                    ->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('qty')->label('Cant.')->numeric(),
                TextColumn::make('subtotal')->label('Subtotal')
                    ->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('created_at')->label('Agregado')->dateTime('d-m-Y H:i'),
            ])
            ->headerActions([]) // usualmente no se crean ítems desde admin
            // ⬇️ Antes: ->actions([...])
            ->recordActions([
                // ViewAction::make(),
                // EditAction::make(),
            ]);
    }
}
