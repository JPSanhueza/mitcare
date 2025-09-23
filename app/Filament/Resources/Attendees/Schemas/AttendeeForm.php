<?php

namespace App\Filament\Resources\Attendees\Schemas;

use App\Models\OrderItem;
use Filament\Forms\Components\DateTimePicker;
// Layout wrappers (v4)
use Filament\Forms\Components\Select;
// Inputs (Forms)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Asistente')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required(),

                    TextInput::make('email')
                        ->label('Correo')
                        ->email()
                        ->required(),

                    Select::make('status')
                        ->label('Estado')
                        ->required()
                        ->options([
                            'pending' => 'Pendiente',
                            'enrolled' => 'Inscrito',
                            'canceled' => 'Cancelado',
                        ]),

                    // vínculo con el OrderItem (curso)
                    Select::make('order_item_id')
                        ->label('Curso')
                        ->relationship('item', 'course_name')
                        ->getOptionLabelFromRecordUsing(fn (OrderItem $r) => "{$r->course_name} — Pedido {$r->order->code}"
                        )
                        ->disabled(fn (string $operation) => $operation === 'edit'), // o ->disabledOn('edit')
                ])->columnSpanFull()
                ->columns(2),

            // Section::make('Moodle')
            //     ->schema([
            //         Toggle::make('moodle_has_account')
            //             ->label('Tiene cuenta en Moodle'),

            //         TextInput::make('moodle_username')
            //             ->label('Usuario Moodle')
            //             ->helperText('Opcional: si difiere del email'),

            //         DateTimePicker::make('moodle_checked_at')
            //             ->label('Fecha de verificación'),
            //     ])->columnSpanFull()
            //     ->columns(3),
        ]);
    }
}
