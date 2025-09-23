<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->copyable()
                    ->grow(false),

                TextColumn::make('buyer_name')
                    ->label('Comprador')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('buyer_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // ⬇️ BadgeColumn -> TextColumn + badge()
                TextColumn::make('payment_method')
                    ->label('Pago')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (mixed $state) => strtoupper((string) $state)),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'  => 'warning',
                        'paid'     => 'success',
                        'failed', 'canceled' => 'danger',
                        'refunded' => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending'  => 'Pendiente',
                        'paid'     => 'Pagado',
                        'failed'   => 'Fallido',
                        'canceled' => 'Cancelado',
                        'refunded' => 'Reembolsado',
                        default    => $state,
                    }),

                TextColumn::make('total')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn (mixed $state) => '$' . number_format((float) $state, 0, ',', '.')),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->grow(false),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options([
                    'pending'  => 'Pendiente',
                    'paid'     => 'Pagado',
                    'failed'   => 'Fallido',
                    'canceled' => 'Cancelado',
                    'refunded' => 'Reembolsado',
                ]),
                Tables\Filters\SelectFilter::make('payment_method')->label('Método de pago')->options([
                    'webpayplus' => 'WebPay',
                    // 'payku'  => 'Payku',
                    // 'flow'   => 'Flow',
                    'manual' => 'Manual',
                ]),
                Tables\Filters\Filter::make('created_between')->label('Fecha')
                    ->schema([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['until'] ?? null, fn ($q, $h) => $q->whereDate('created_at', '<=', $h));
                    }),
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
