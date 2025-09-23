<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Datos del curso
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    if (blank($state)) {
                        return;
                    }
                    $set('slug', Str::slug($state));
                }),

            TextInput::make('subtitulo')
                ->label('Subtítulo')
                ->maxLength(255)
                ->nullable(),

            RichEditor::make('descripcion')
                ->label('Descripción')
                ->columnSpanFull(),

            // Venta & publicación
            TextInput::make('price')
                ->label('Precio')
                ->required()
                ->numeric()
                ->minValue(0)
                ->prefix('CLP'),

            Toggle::make('is_active')
                ->label('Activo')
                ->default(true),

            DateTimePicker::make('published_at')
                ->label('Publicado desde')
                ->seconds(false),

            TextInput::make('capacity')
                ->label('Cupos')
                ->numeric()
                ->minValue(1)
                ->helperText('Déjalo vacío si no hay límite.')
                ->nullable(),

            // Ejecución
            Select::make('modality')
                ->label('Modalidad')
                ->options([
                    'online' => 'Online',
                    'presencial' => 'Presencial',
                    'mixto' => 'Mixto',
                ])
                ->default('online')
                ->required(),

            DateTimePicker::make('start_at')
                ->label('Inicio')
                ->seconds(false),

            DateTimePicker::make('end_at')
                ->label('Término')
                ->seconds(false)
                ->rule('after_or_equal:start_at'),

            TextInput::make('location')
                ->label('Ubicación')
                ->maxLength(255)
                ->visible(fn (callable $get) => in_array($get('modality'), ['presencial', 'mixto'])),

            // Medios & externos
            FileUpload::make('image')
                ->label('Imagen (portada)')
                ->image()
                ->disk('public')
                ->directory('courses')
                ->imageEditor(),

            TextInput::make('order')
                ->label('Orden')
                ->required()
                ->numeric()
                ->default('0'),

            // TextInput::make('external_url')
            //     ->label('URL externa')
            //     ->url()
            //     ->maxLength(255),

            // TextInput::make('moodle_course_id')
            //     ->label('ID Moodle')
            //     ->maxLength(255),
        ]);
    }
}
