<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Models\Course;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Actions\Action;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Imagen')->circular(),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->money('CLP')
                    ->sortable(),

                TextColumn::make('modality')
                    ->label('Modalidad')
                    ->badge()
                    ->colors([
                        'success' => 'online',
                        'warning' => 'mixto',
                        'info'    => 'presencial',
                    ])
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Activos')->boolean(),
                SelectFilter::make('modality')->label('Modalidad')->options([
                    'online' => 'Online',
                    'presencial' => 'Presencial',
                    'mixto' => 'Mixto',
                ]),
            ])
            // ->recordActions([
            //     ViewAction::make(),
            //     EditAction::make(),
            //     Action::make('ver_publico')
            //         ->label('Ver público')
            //         ->icon('heroicon-m-arrow-top-right-on-square')
            //         ->url(fn (Course $r) => url("/cursos/{$r->slug}"))
            //         ->openUrlInNewTab(),
            // ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
