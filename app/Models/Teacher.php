<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'foto',
        'especialidad',
        'email',
        'telefono',
        'especialidad',
        'is_active',
    ];
}
