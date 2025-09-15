<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class TeacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            FileUpload::make('foto')
                ->label('Foto')
                ->image()
                ->disk('public')
                ->directory('teachers')
                ->imageEditor(),

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

            Textarea::make('especialidad')
                ->label('Especialidad')
                ->rows(2)
                ->nullable(),

            Textarea::make('descripcion')
                ->label('Descripción')
                ->rows(5)
                ->nullable(),
        ]);
    }
}
