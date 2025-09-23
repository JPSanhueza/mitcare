<?php

namespace App\Filament\Resources\Attendees\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class AttendeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Group::make([
                Section::make('Asistente')->schema([
                    TextEntry::make('name')->label('Nombre'),
                    TextEntry::make('email')->label('Email'),
                    TextEntry::make('status')->label('Estado')
                        ->badge()
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
                    TextEntry::make('item.order.code')->label('Pedido'),
                    TextEntry::make('item.course_name')->label('Curso'),
                ])->columns(3),

                // Section::make('Moodle')->schema([
                //     IconEntry::make('moodle_has_account')
                //         ->label('Cuenta en Moodle')
                //         ->boolean()
                //         ->trueIcon('heroicon-o-check-circle')
                //         ->falseIcon('heroicon-o-x-circle'),
                //     TextEntry::make('moodle_username')->label('Usuario Moodle')->placeholder('—'),
                //     TextEntry::make('moodle_checked_at')->label('Verificado el')->dateTime('d-m-Y H:i')->placeholder('—'),
                // ])->columns(3),
            ])->columnSpanFull(),
        ]);
    }
}
