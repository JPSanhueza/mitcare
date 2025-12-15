<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use App\Rules\ValidRut;
use Closure;
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
                    ->label('RUT (Opcional)')
                    ->nullable()
                    ->placeholder('12.345.678-5')
                    ->maxLength(20)
                    // ✅ Solo formato + DV (tu regla personalizada)
                    ->rules([new ValidRut])
                    // ✅ Unicidad (ignorando el registro actual)
                    ->rule(function (?Student $record) {
                        return function (string $attribute, $value, Closure $fail) use ($record) {
                            if (blank($value)) {
                                return; // si no hay RUT, no validar unicidad
                            }

                            $normalized = Student::normalizeRut((string) $value);

                            $query = Student::query()->where('rut', $normalized);

                            if ($record) {
                                $query->whereKeyNot($record->getKey());
                            }

                            if ($query->exists()) {
                                $fail('El RUT ingresado ya está registrado.');
                            }
                        };
                    })

                    // Mostrar formateado al editar
                    ->afterStateHydrated(function (TextInput $component, $state) {
                        if ($state) {
                            $component->state(Student::formatRut($state));
                        }
                    })
                    // Guardar siempre normalizado
                    ->dehydrateStateUsing(function ($state) {
                        if (blank($state)) {
                            return null; // se guarda como NULL en BD
                        }

                        return Student::normalizeRut($state);
                    }),

                TextInput::make('password')
                    ->label('Contraseña (opcional)')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn($state) => $state ?: null)
                    ->helperText('Al crear el Estudiante, se enviará una invitación por Email para cambiar su contraseña.')
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(
                        table: 'students',
                        column: 'email',
                        ignoreRecord: true
                    )
                    ->helperText('Se utilizará el Email para iniciar sesión.')

                ,

                DateTimePicker::make('email_verified_at')
                    ->label('Fecha verificación de email')
                    ->helperText('Solo marcar si se verificó el correo por otro canal.')
                    ->native(false)
                    ->hidden(),

                // TextInput::make('telefono')
                //     ->label('Teléfono')
                //     ->tel()
                //     ->maxLength(20),

                // TextInput::make('direccion')
                //     ->label('Dirección')
                //     ->maxLength(255),
            ]);
    }
}
