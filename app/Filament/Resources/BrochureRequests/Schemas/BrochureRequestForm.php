<?php

namespace App\Filament\Resources\BrochureRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BrochureRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('Curso')
                    ->relationship('course', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('full_name')
                    ->label('Nombre completo')
                    ->required()
                    ->maxLength(120),

                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->maxLength(190),

                TextInput::make('phone')
                    ->label('Teléfono (código país + número)')
                    ->required()
                    ->placeholder('+56912345678')
                    ->helperText('Formato internacional E.164. Ej: +56912345678')
                    ->maxLength(40)
                    // ✅ valida + y dígitos (7 a 15 dígitos después del +)
                    ->rule('regex:/^\+[1-9]\d{6,14}$/')
                    // ✅ opcional: normaliza espacios/guiones antes de guardar
                    ->dehydrateStateUsing(function ($state) {
                        $state = (string) $state;
                        // remueve espacios, guiones, paréntesis
                        $state = preg_replace('/[\s\-\(\)]+/', '', $state);
                        return $state;
                    }),
            ]);
    }
}
