<?php

namespace App\Rules;

use App\Models\Student;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRut implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1) Normalizar
        $rutNormalizado = Student::normalizeRut((string) $value);

        // 2) Validar formato (DV correcto)
        if (!Student::isValidRut($rutNormalizado)) {
            $fail('El RUT ingresado no es válido.');
            return;
        }

        // 3) Validar duplicado
        /* $query = Student::where('rut', $rutNormalizado);

        // Si estamos editando en Filament, la ruta trae {record}
        $currentId = request()->route('record') ?? request()->route('id');

        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }

        if ($query->exists()) {
            $fail('El RUT ingresado ya está registrado.');
        } */
    }
}
