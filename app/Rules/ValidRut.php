<?php

namespace App\Rules;

use App\Models\Student;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRut implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Si está vacío, no validar nada (lo permite)
        if (blank($value)) {
            return;
        }

        $rutNormalizado = Student::normalizeRut((string) $value);

        if (! Student::isValidRut($rutNormalizado)) {
            $fail('El RUT ingresado no es válido.');
            return;
        }
    }
}
