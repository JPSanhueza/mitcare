<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TeacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            TextInput::make('apellido')
                ->label('Apellido')
                ->required()
                ->maxLength(255),

            FileUpload::make('foto')
                ->label('Foto')
                ->image()
                ->disk('public')
                ->directory('teachers/photos')
                ->imageEditor()
                ->rules([
                    'image',
                    'mimes:jpg,jpeg,png',
                ])
                ->validationMessages([
                    'image' => 'El archivo debe ser una imagen válida.',
                    'mimes' => 'El formato de la imagen debe ser jpg, jpeg o png.',
                    'max' => 'La imagen no puede exceder los 1024 KB (1 MB).',
                ])
                ->maxSize(1024),

            FileUpload::make('signature')
                ->label('Firma')
                ->image()
                ->disk('public')
                ->directory('teachers/signatures')
                ->imageEditor()
                ->imageEditorViewportWidth('1080')
                ->imageEditorViewportHeight('1080')
                ->rules([
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                ])
                ->validationMessages([
                    'image' => 'El archivo debe ser una imagen válida.',
                    'mimes' => 'El formato de la imagen debe ser jpg, jpeg, png o webp.',
                    // 'dimensions' => 'La imagen debe tener una relación de aspecto de 1:1 (edita para corregir).',
                    'max' => 'La imagen no puede exceder los 1024 KB (1 MB).',
                ])
                ->maxSize(1024),

            TextInput::make('especialidad')
                ->label('Especialidad/Cargo')
                ->nullable(),

            TextInput::make('organization_name')
                ->label('Organización')
                ->maxLength(255)
                ->nullable(),

            TextInput::make('order')
                ->label('Orden')
                ->required()
                ->numeric()
                ->default('0'),

            Toggle::make('is_active')
                ->label('Activo')
                ->default(true),

            // TextInput::make('email')
            //     ->label('Correo')
            //     ->email()
            //     ->unique(ignoreRecord: true)
            //     ->nullable(),

            // TextInput::make('telefono')
            //     ->label('Teléfono')
            //     ->maxLength(50)
            //     ->nullable(),

            // Textarea::make('descripcion')
            //     ->label('Descripción')
            //     ->rows(5)
            //     ->nullable(),
        ]);
    }
}
