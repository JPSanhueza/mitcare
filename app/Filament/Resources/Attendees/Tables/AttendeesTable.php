<?php

namespace App\Filament\Resources\Attendees\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('item.order.code')
                    ->label('Pedido')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')->label('Estado')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'enrolled' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'enrolled' => 'Inscrito',
                        'canceled' => 'Cancelado',
                        default => $state,
                    }),

                // Toggle rápido de control Moodle
                ToggleColumn::make('moodle_has_account')
                    ->label('Verificado Aula Virtual')
                    ->afterStateUpdated(function (bool $state, $record): void {
                        // Si se marca / desmarca, dejamos constancia de cuándo se verificó
                        $record->moodle_checked_at = Carbon::now();
                        $record->save();
                    }),

                TextColumn::make('item.course_name')->label('Curso')->toggleable(),

                TextColumn::make('moodle_username')->label('Usuario Moodle')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('moodle_checked_at')->label('Verificado el')
                    ->dateTime('d-m-Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')->label('Creado')->dateTime('d-m-Y H:i')->sortable()->grow(false),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options([
                    'pending' => 'Pendiente',
                    'enrolled' => 'Inscrito',
                    'canceled' => 'Cancelado',
                ]),

                // Filtrar por control Moodle (sí / no / desconocido)
                Tables\Filters\TernaryFilter::make('moodle_has_account')
                    ->label('Cuenta Moodle')
                    ->trueLabel('Con cuenta')
                    ->falseLabel('Sin cuenta')
                    ->placeholder('Desconocido'),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkAction::make('deleteSelected')
                    ->label('Eliminar seleccionados')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->delete())
                    ->deselectRecordsAfterCompletion(),
            ])

            ->defaultSort('created_at', 'desc');
    }
}
