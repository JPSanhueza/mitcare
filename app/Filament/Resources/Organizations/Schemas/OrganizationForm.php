<?php

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            FileUpload::make('logo')
                ->label('Logo')
                ->disk('public')
                ->directory('organizations/logos')
                ->visibility('public')
                ->image()
                ->imageEditor()
                ->rules([
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                ])
                ->validationMessages([
                    'image' => 'El archivo debe ser una imagen válida.',
                    'mimes' => 'El formato de la imagen debe ser jpg, jpeg, png o webp.',
                    'max' => 'La imagen no puede exceder los 1024 KB (1 MB).',
                ])
                ->maxSize(1024)
                ->helperText('Sube un logo en JPG, PNG o WEBP. Máx 1MB.'),
        ]);
    }
}
