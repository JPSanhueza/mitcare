<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
// Layout wrappers (v4)
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
// Infolist entries
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Group::make([
            Section::make('Pedido')
                ->schema([
                    TextEntry::make('code')->label('Código')->copyable(),
                    TextEntry::make('buyer_name')->label('Comprador'),
                    TextEntry::make('buyer_email')->label('Correo'),

                    TextEntry::make('payment_method')
                        ->label('Método de pago')
                        ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                    TextEntry::make('status')
                        ->label('Estado')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'failed', 'canceled' => 'danger',
                            'refunded' => 'gray',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending' => 'Pendiente',
                            'paid' => 'Pagado',
                            'failed' => 'Fallido',
                            'canceled' => 'Cancelado',
                            'refunded' => 'Reembolsado',
                            default => $state,
                        }),

                    TextEntry::make('currency')->label('Moneda'),
                ])
                ->columns(3),

            Section::make('Montos')
                ->schema([
                    TextEntry::make('subtotal')
                        ->label('Subtotal')
                        ->formatStateUsing(fn (mixed $state): string => '$' . number_format((float) $state, 0, ',', '.')),

                    TextEntry::make('total')
                        ->label('Total')
                        ->formatStateUsing(fn (mixed $state): string => '$' . number_format((float) $state, 0, ',', '.')),
                ])
                ->columns(2),
            ])
            ->columnSpanFull(),

            // Abajo: Meta
            // Section::make('Meta')
            //     ->schema([
            //         KeyValueEntry::make('meta')->label('Meta')->columns(2),
            //     ])
            //     ->collapsible(),
        ]);
    }
}
