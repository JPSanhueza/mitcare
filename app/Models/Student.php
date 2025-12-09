<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'rut',
        'password',
        'telefono',
        'direccion',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        /* -------------------------
          CREATING
        ------------------------- */
        static::creating(function (Student $student) {
            try {
                // Normalizar RUT (si viene)
                if (!empty($student->rut)) {
                    $student->rut = static::normalizeRut($student->rut);

                    if (!static::isValidRut($student->rut)) {
                        throw ValidationException::withMessages([
                            'rut' => 'El RUT ingresado no es válido.',
                        ]);
                    }
                } else {
                    $student->rut = null;
                }

                // Email
                if (!empty($student->email)) {
                    $student->email = strtolower(trim($student->email));
                }

                // Password interna si no viene
                if (empty($student->password)) {
                    $student->password = Str::random(32);
                }

                if ($student->must_change_password === null) {
                    $student->must_change_password = true;
                }

            } catch (Throwable $e) {
                throw ValidationException::withMessages([
                    'error' => 'Error al crear estudiante: ' . $e->getMessage(),
                ]);
            }
        });

        /* -------------------------
          UPDATING
        ------------------------- */
        static::updating(function (Student $student) {
            try {
                // Si cambian el RUT
                if ($student->isDirty('rut')) {

                    if (blank($student->rut)) {
                        $student->rut = null;
                    } else {
                        $student->rut = static::normalizeRut($student->rut);

                        if (!static::isValidRut($student->rut)) {
                            throw ValidationException::withMessages([
                                'rut' => 'El RUT ingresado no es válido.',
                            ]);
                        }
                    }
                }

                // Si cambian el email
                if ($student->isDirty('email') && !empty($student->email)) {
                    $student->email = strtolower(trim($student->email));
                }

            } catch (Throwable $e) {
                throw ValidationException::withMessages([
                    'student' => 'Error inesperado al actualizar estudiante: ' . $e->getMessage(),
                ]);
            }
        });
    }

    /* -------------------------
      MUTATOR PASSWORD
    ------------------------- */
    public function setPasswordAttribute($value)
    {
        try {
            if (!empty($value)) {
                $this->attributes['password'] = Hash::needsRehash($value)
                    ? Hash::make($value)
                    : $value;
            }
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'password' => 'Error al establecer la contraseña.',
            ]);
        }
    }


    /* Relaciones */
    public function courses()
    {
        return $this->belongsToMany(Course::class)
            ->using(CourseStudent::class)
            ->withPivot([
                'enrolled_at',
                'final_grade',
                'approved',
                'attendance',
                'diploma_issued',
            ])
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(CourseStudent::class);
    }

    public function diplomas()
    {
        return $this->hasMany(Diploma::class);
    }


    /* -------------------------
      RUT HELPERS
    ------------------------- */

    public static function normalizeRut(?string $rut): string
    {
        if (blank($rut)) {
            return '';
        }

        return strtoupper(preg_replace('/[^0-9kK]/', '', $rut));
    }

    public static function isValidRut(?string $rut): bool
    {
        if (empty($rut) || strlen($rut) < 2) {
            return false;
        }

        $rut = strtoupper($rut);

        $dv = substr($rut, -1);
        $cuerpo = substr($rut, 0, -1);

        if (!ctype_digit($cuerpo)) {
            return false;
        }

        $suma = 0;
        $factor = 2;

        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += intval($cuerpo[$i]) * $factor;
            $factor = ($factor === 7) ? 2 : $factor + 1;
        }

        $resto = $suma % 11;
        $digitoCalculado = 11 - $resto;

        $dvEsperado = match ($digitoCalculado) {
            11 => '0',
            10 => 'K',
            default => (string) $digitoCalculado
        };

        return $dv === $dvEsperado;
    }

    public static function formatRut(?string $rut): ?string
    {
        if (empty($rut)) {
            return $rut;
        }

        $rut = self::normalizeRut($rut);

        if (strlen($rut) < 2) {
            return $rut;
        }

        $dv = substr($rut, -1);
        $cuerpo = substr($rut, 0, -1);

        $cuerpoReverso = strrev($cuerpo);
        $chunks = str_split($cuerpoReverso, 3);
        $cuerpoConPuntos = strrev(implode('.', $chunks));

        return $cuerpoConPuntos . '-' . $dv;
    }
}
