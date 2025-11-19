<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /* ---------------------------------------
       BOOT: generar password automáticamente
    --------------------------------------- */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {

            // Si no viene password manual, la generamos
            if (empty($student->password)) {

                // Limpiar el RUT (solo números y k si existiera)
                $rutLimpio = preg_replace('/[^0-9kK]/', '', $student->rut);

                // Tomar los primeros 6 dígitos
                $primeros6 = substr($rutLimpio, 0, 6);

                // Nombre: primeros 2 caracteres (respetando mayúscula)
                $dosLetras = substr($student->nombre, 0, 2);

                // Unimos todo
                $passwordPlano = $primeros6 . $dosLetras;

                // Hashear
                $student->password = Hash::make($passwordPlano);
            }
        });
    }

    /* ---------------------------------------
       MUTATOR: asegurar hash si viene manual
    --------------------------------------- */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::needsRehash($value)
                ? Hash::make($value)
                : $value;
        }
    }

    /* ---------------------------------------
       RELACIONES
    --------------------------------------- */

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
}
