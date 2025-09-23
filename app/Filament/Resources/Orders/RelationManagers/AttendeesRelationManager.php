<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendees';
    protected static ?string $title = 'Asistentes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('item.course_name')->label('Curso'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'  => 'warning',
                        'enrolled' => 'success',
                        'canceled' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending'  => 'Pendiente',
                        'enrolled' => 'Inscrito',
                        'canceled' => 'Cancelado',
                        default    => $state,
                    }),
                TextColumn::make('created_at')->label('Creado')
                ->toggleable(isToggledHiddenByDefault: true)
                ->dateTime('d-m-Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending'  => 'Pendiente',
                    'enrolled' => 'Inscrito',
                    'canceled' => 'Cancelado',
                ]),
            ])
            ->recordActions([
                Action::make('editar')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Nombre')->required(),
                        Forms\Components\TextInput::make('email')->label('Email')->email()->required(),
                        Forms\Components\Select::make('status')->label('Estado')->required()->options([
                            'pending'  => 'Pendiente',
                            'enrolled' => 'Inscrito',
                            'canceled' => 'Cancelado',
                        ]),
                    ])
                    ->fillForm(fn ($record) => [
                        'name'   => $record->name,
                        'email'  => $record->email,
                        'status' => $record->status,
                    ])
                    ->action(fn ($record, array $data) => $record->update($data)),
            ]);
    }
}
