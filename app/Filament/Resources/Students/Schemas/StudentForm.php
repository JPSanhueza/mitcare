<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use App\Rules\ValidRut;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre(s)')
                    ->required()
                    ->maxLength(100),

                TextInput::make('apellido')
                    ->label('Apellido(s)')
                    ->required()
                    ->maxLength(100),

                TextInput::make('rut')
                    ->label('RUT (usuario de acceso)')
                    ->required()
                    ->placeholder('12.345.678-5')
                    ->maxLength(20)
                    // ✅ Validación de formato + duplicado
                    ->rules([new ValidRut()])
                    // ✅ Al hidratar (editar), mostrar con puntos y guion
                    ->afterStateHydrated(function (TextInput $component, $state) {
                        if ($state) {
                            $component->state(Student::formatRut($state));
                        }
                    })
                    // ✅ Antes de guardar, normalizar (sin puntos ni guion)
                    ->dehydrateStateUsing(fn($state) => Student::normalizeRut($state))
                    ->helperText('Este será el usuario con el que el estudiante iniciará sesión para descargar su diploma. Puedes escribir el RUT con puntos y guion.'),

                TextInput::make('password')
                    ->label('Contraseña (opcional)')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn($state) => $state ?: null)
                    ->helperText('Si se deja en blanco, el sistema generará automáticamente una contraseña usando los 6 primeros dígitos del RUT y las 2 primeras letras del nombre.')
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email (opcional)')
                    ->email()
                    ->nullable()
                    ->maxLength(255)
                    ->unique(
                        table: 'students',
                        column: 'email',
                        ignoreRecord: true
                    )
                    ->helperText('Solo si deseas tener un correo de contacto. No se utiliza para ingresar al sistema.'),

                DateTimePicker::make('email_verified_at')
                    ->label('Fecha verificación de email')
                    ->helperText('Solo marcar si se verificó el correo por otro canal.')
                    ->native(false)
                    ->hidden(),

                TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20),

                TextInput::make('direccion')
                    ->label('Dirección')
                    ->maxLength(255),
            ]);
    }
}
