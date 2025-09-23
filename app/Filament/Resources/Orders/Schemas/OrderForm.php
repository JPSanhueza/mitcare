<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Datos del pedido')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')->label('Código')->readOnly(),
                        TextInput::make('buyer_name')->label('Comprador'),
                        TextInput::make('buyer_email')->label('Email')->email(),
                        Select::make('payment_method')->label('Método de pago')->disabled()
                            ->options([
                                'webpayplus' => 'WebPay',
                                // 'payku'  => 'Payku',
                                // 'flow'   => 'Flow',
                                'manual' => 'Manual',
                            ]),
                        Select::make('status')->label('Estado')->required()
                            ->options([
                                'pending'   => 'Pendiente',
                                'paid'      => 'Pagado',
                                'failed'    => 'Fallido',
                                'canceled'  => 'Cancelado',
                                'refunded'  => 'Reembolsado',
                            ]),
                    ]),

                Section::make('Montos')
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')->numeric()->readOnly()
                            ->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 0, ',', '.')),
                        TextInput::make('total')->numeric()->readOnly()
                            ->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 0, ',', '.')),
                        TextInput::make('currency')->label('Moneda')->maxLength(3)->readOnly(),
                    ]),

                // KeyValue::make('meta')->label('Meta'),
            ]);
    }
}
